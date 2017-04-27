#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from tornado import gen, ioloop
from tornado.concurrent import Future
from functools import partial
import time
import json

from core import mysql_pool, redis_client, WWSHandler


class CommentOp(object):
    @gen.coroutine
    def add_comment(self, comment, article_ID, user_ID, time):
        result = yield redis_client.call("Rpush", "comment:" + str(article_ID),\
        json.dumps(dict(uid=user_ID, time = time)) )
        print('add to:')
        print(result)
    
    @gen.coroutine
    def get_comments(self, article_ID):
        result = yield redis_client.call( "LRANGE", "comment:" + str(article_ID), 0, -1)
        raise gen.Return(result) 

        
cmt_op = CommentOp()

class CommentBuffer(object):
    def __init__(self):
        self.waiters = set()
        self.cache = []
        self.cache_size = 200

    @gen.coroutine
    def wait_for_comments(self, article, cursor = None):
        result_future = Future()
        new_count = 0
        cmts = yield cmt_op.get_comments(article)
        if cursor:
            for cmt in reversed(self.cache):
                if cmt["time"] == cursor:
                    break
                new_count += 1
        else:
            new_count = len(self.cache)

        if new_count:
            result_future.set_result(self.cache[-new_count:])
            raise gen.Return(result_future)
        self.add_wait(result_future)
        raise gen.Return(result_future)

    def add_wait(self, future):
        self.waiters.add(future)
        ioloop.PeriodicCallback( partial( self.cancel_wait, future), 40000).start()

    def cancel_wait(self, future):
        if future.running():
            self.waiters.remove(future)
            future.set_result([])
    
    def new_comments(self, comment):
        for future in self.waiters:
            future.set_result(comment)
        self.waiters = set()
        self.cache.extend([comment])
        if len(self.cache) > self.cache_size:
            self.cache = self.cache[-self.cache_size:]

        cmt_op.add_comment(comment = comment['comment'], article_ID = comment['article'], user_ID = comment['user'], time = comment['time'])



global_comment_buffer = CommentBuffer()

class CommentHandler(WWSHandler):
    @gen.coroutine
    def get(self):
        cursor = self.get_argument("cursor",None)
        article = self.get_arguments("article")
        self.future = yield global_comment_buffer.wait_for_comments(article=article, cursor=cursor)
        comment = yield self.future
        if self.request.connection.stream.closed():
            return
        self.write(dict(comments = comment))

    def on_connection_close(self):
        global_comment_buffer.cancel_wait(self.future)
        
    @gen.coroutine
    def put(self):
        user = yield self.current_user

        if user is None:
            self.write(dict(status = "error", reason = "no_login"))
            self.finish()
            return 

        comment = {
            "user": user[0],
            "article": self.get_argument("article"),
            "time" : str(time.time()),
            "comment" : self.get_argument("comment"),
        }
        self.write(comment)
        global_comment_buffer.new_comments(comment)
