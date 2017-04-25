#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import tornado.websocket
import tornado.httpserver
import tornado.options
import base64
import time
import os
import tornadis
import tormysql
import json
import bcrypt

from urllib.parse import unquote
from functools import partial
from langdetect import detect
from uuid import uuid4
from tornado import gen, web ,ioloop
from tornado.concurrent import Future

from tornado.options import define, options
define("port", default=8000, help="run on the given port", type=int)

redis_client = tornadis.Client(host="45.76.176.44", port=6379, password="lq931110", autoconnect=True)
pool = tormysql.helpers.ConnectionPool(
    max_connections = 20,
    idle_seconds = 0, #conntion idle timeout time
    wait_connection_timeout = 3, #wait connection timeout
    host = "45.76.176.44",
    user = "root",
    passwd = "lq931110",
    db = "wws",
    charset = "utf8"
)
class Op(object):
    pass

class CommentOp(object):
    @gen.coroutine
    def add_comment(self, comment, article_ID, user_ID, time):
        result = yield redis_client.call("Rpush", "comment:" + str(article_ID),\
        json.dumps(dict(uid=user_ID, time = time)) )
    
    @gen.coroutine
    def get_comments(self, article_ID):
        result = yield redis_client.call( "LRANGE", "comment:" + str(article_ID), 0, -1)


class LikeOp(Op):
    @gen.coroutine
    def like(self, article_ID, user_ID):
        result = yield redis_client.call("HSETNX" , "LIKES", str(article_ID), 0)
        result = yield redis_client.call("HINCRBY", "LIKES", str(article_ID), 1)
        sql = yield pool.begin()
        try: 
            yield sql.execute("INSERT INTO `term_rel`(`rel`,`ID_1`,`ID_2`) " +
                    "values('like_article_user',%d,%d)" % (article_ID,user_ID))
        except:
            yield sql.rollback()
        else:
            yield sql.commit()
         
        raise gen.Return(True)


class FEFHandler(tornado.web.RequestHandler):
    def get(self):
        self.write(dict(status = "error", reason = "no_login"))

    def post(self):
        self.write(dict(status = "error", reason = "no_login"))

class WWSHandler(tornado.web.RequestHandler):
    @gen.coroutine
    def get_current_user(self):
        if hasattr(self, "_current_user"):
            raise gen.Return(self._current_user)

        self._current_user = None
        login_cookie = self.get_cookie('logined')
        if login_cookie is None:
            raise gen.Return(None)

        login_cookie = unquote(login_cookie)
        splited = login_cookie.split('|')
        if len(splited) != 2 :
            raise gen.Return(None)
                
        user_login = splited[0]
        user_pwd = splited[1]
        user_IP = yield redis_client.call("GET", "user:" + user_login)
        if user_IP == self.request.remote_ip:
            raise gen.Return(user_login) 

        cursor = yield pool.execute(\
        "SELECT * FROM `users` WHERE `user_login` = '%s'"%(user_login))
        user = cursor.fetchall()
        if len(user) != 1:
            raise gen.Return(None)

        if bcrypt.hashpw(user[0][2].encode("utf-8"), user_pwd.encode("utf-8")) == user_pwd.encode("utf-8"):
            self._current_user = user[0][1]
            yield redis_client.call("SET", "user:" + user_login, self.request.remote_ip)
            yield redis_client.call("EXPIRE", "user:" + user_login, "1800") 
            

        raise gen.Return(self._current_user)



class LikeHandler(WWSHandler):
    
    def get_login_uri(self):
        return r"/404.py"

    @gen.coroutine
    def post(self):
        user = yield self.current_user

        if user is None:
            self.write(dict(status = "error", reason = "no_login"))
            self.finish()
            return 

        article = self.get_arguments("article")
        result = yield redis_client.call("HSETNX" , "LIKE", str(article), 0)
        result = yield redis_client.call("HINCRBY", "LIKE", str(article), 1)
        self.write(dict(status = "ok", likes = result))
        self.finish()
        article = 21
        sql = yield pool.begin()
        try: 
            yield sql.execute("INSERT INTO `term_rel`(`rel`,`ID_1`,`ID_2`) " +
                    "values('like_article_user',%d,%d)" % (article,user[0]))
        except:
            yield sql.rollback()
        else:
            yield sql.commit()

            

class DetectLang(tornado.web.RequestHandler):
    def post(self):
        text = self.request.body
        self.write(detect(text.decode('UTF-8'))[:2])

class StatusHandler(tornado.websocket.WebSocketHandler):
    pass

class CommentBuffer(object):
    def __init__(self):
        self.waiters = set()
        self.cache = []
        self.cache_size = 200

    def wait_for_comments(self, article, cursor = None):
        result_future = Future()
        new_count = 0
        if cursor:
            for cmt in reversed(self.cache):
                if cmt["time"] == cursor:
                    break
                new_count += 1
        else:
            new_count = len(self.cache)

        if new_count:
            result_future.set_result(self.cache[-new_count:])
            return result_future
        self.add_wait(result_future)
        return result_future

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

        c_op = CommentOp()
        c_op.add_comment(comment = comment['comment'], article_ID = comment['article'], user_ID = comment['user'], time = comment['time'])
        c_op.get_comments(1)

global_comment_buffer = CommentBuffer()


class CommentHandler(WWSHandler):
    @gen.coroutine
    def get(self):
        cursor = self.get_argument("cursor",None)
        article = self.get_arguments("article")
        self.future = global_comment_buffer.wait_for_comments(article=article, cursor=cursor)
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
            "article": self.get_argument("comment"),
            "time" : str(time.time()),
            "comment" : self.get_argument("comment"),
        }
        self.write(comment)
        global_comment_buffer.new_comments(comment)


class ImageHandle(tornado.web.RequestHandler):
    async def put(self):
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


if __name__ == '__main__':
    tornado.options.parse_command_line()

    app = web.Application(handlers=[
        (r"/api/detection",DetectLang),
        (r"/api/status",StatusHandler),
        (r"/api/comment",CommentHandler),
        (r"/api/like",LikeHandler),
        (r"/api/image",ImageHandle),
        (r"/api/404",FEFHandler)
        ], debug = False)
    http_server = tornado.httpserver.HTTPServer(app)
    http_server.listen(options.port)
    ioloop.IOLoop.instance().start()

    pool.close()
