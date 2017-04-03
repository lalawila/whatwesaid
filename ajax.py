#!/usr/bin/env python3
# -*- coding: utf-8 -*-


import tornado.web
import tornado.websocket
import tornado.httpserver
import tornado.ioloop
import tornado.options
from langdetect import detect
from uuid import uuid4

from tornado.options import define, options
define("port", default=8000, help="run on the given port", type=int)


class Detect_lang(tornado.web.RequestHandler):
    def post(self):
        text = self.request.body
        self.write(detect(text.decode('UTF-8'))[:2])

class StatusHandler(tornado.websocket.WebSocketHandler):
    pass

class CommentHandler(tornado.web.RequestHandler)
   def post(self):



if __name__ == '__main__':
    tornado.options.parse_command_line()

    app = tornado.web.Application(handlers=[
        (r"/detect.py",Detect_lang),
        (r"/status.py",StatusHandler),
        (r"/comment.py",CommentHandler)
        ])
    http_server = tornado.httpserver.HTTPServer(app)
    http_server.listen(options.port)
    tornado.ioloop.IOLoop.instance().start()
