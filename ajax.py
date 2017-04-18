#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import tornado.websocket
import tornado.httpserver
import tornado.options
import base64
import time
import os

from functools import partial
from langdetect import detect
from uuid import uuid4
from tornado import gen, web ,ioloop
from tornado.concurrent import Future

from tornado.options import define, options
define("port", default=8000, help="run on the given port", type=int)


class DetectLang(tornado.web.RequestHandler):
    def post(self):
        text = self.request.body
        print(text);
        self.write(detect(text.decode('UTF-8'))[:2])

class StatusHandler(tornado.websocket.WebSocketHandler):
    pass

class CommentBuffer(tornado.web.RequestHandler):
    def __init__(self):
        self.waiters = set()
        self.cache = []
        self.cache_size = 200

    def wait_for_comments(self, article, cursor = None):
        result_future = Future()
        print(cursor);
        new_count = 0
        if cursor:
            for cmt in reversed(self.cache):
                print(cmt["time"])
                if cmt["time"] == cursor:
                    break
                new_count += 1
        else:
            new_count = len(self.cache)
        print('have count' + str(new_count))

        if new_count:
            result_future.set_result(self.cache[-new_count:])
            return result_future
        self.add_wait(result_future)
        #self.waiters.add(result_future)
        return result_future

    def add_wait(self, future):
        self.waiters.add(future)
        ioloop.PeriodicCallback( partial( self.cancel_wait, future), 40000).start()

    def cancel_wait(self, future):
        if future.running():
            print('cancel')
            self.waiters.remove(future)
            future.set_result([])
    
    def new_comments(self, comments):
        for future in self.waiters:
            future.set_result(comments)
        self.waiters = set()
        self.cache.extend(comments)
        print(self.cache)
        if len(self.cache) > self.cache_size:
            self.cache = self.cache[-self.cache_size:]

global_comment_buffer = CommentBuffer()


class CommentNewHandler(tornado.web.RequestHandler):
    def post(self):
        comment = {
            "time" : str(time.time()),
            "comment" : self.get_argument("comment"),
        }
        self.write(comment)
        global_comment_buffer.new_comments([comment])

class CommentUpdatesHandler(tornado.web.RequestHandler):
    @gen.coroutine
    def post(self):
        cursor = self.get_argument("cursor",None)
        article = self.get_arguments("article")
        self.future = global_comment_buffer.wait_for_comments(article=article, cursor=cursor)
        comments = yield self.future
        if self.request.connection.stream.closed():
            return
        self.write(dict(comments = comments))

    def on_connection_close(self):
        global_comment_buffer.cancel_wait(self.future)


class ImageSave(tornado.web.RequestHandler):
    async def post(self):
        upload_path = os.path.join('content', 'image')
        file_metas=self.request.files['inputfile']
        for meta in file_metas:
            name = str(base64.urlsafe_b64encode(uuid4().bytes))
            name = name[2:-1] + '.'
            if meta['content_type'] in ['image/png', 'image/jpg', 'image/jpeg', 'image/gif']:
                name += meta['content_type'][6:]
            elif meta['content_type'] == 'image/svg+xml':
                name += 'svg'
            else:
                self.set_status(403)
                self.write('It is not image file.')
                return
            
            filepath = os.path.join(upload_path, name)

            with open(filepath,'wb') as file:
                file.write(meta['body'])
            self.write('\\' + filepath)

            print(filepath)

if __name__ == '__main__':
    tornado.options.parse_command_line()

    app = web.Application(handlers=[
        (r"/detect.py",DetectLang),
        (r"/status.py",StatusHandler),
        (r"/comment/new.py",CommentNewHandler),
        (r"/comment/updates.py",CommentUpdatesHandler),
        (r"/image.py",ImageSave)
        ])
    http_server = tornado.httpserver.HTTPServer(app)
    http_server.listen(options.port)
    ioloop.IOLoop.instance().start()
