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
from uuid import uuid4
from tornado import gen, web ,ioloop
from tornado.concurrent import Future

#my lib
from comment import CommentHandler 
from detection import DetectHandler
from image import ImageHandler
from like import LikeHandler

from tornado.options import define, options
define("port", default=8000, help="run on the given port", type=int)


class FEFHandler(tornado.web.RequestHandler):
    def get(self):
        self.write(dict(status = "error", reason = "no_login"))

    def post(self):
        self.write(dict(status = "error", reason = "no_login"))

class StatusHandler(tornado.websocket.WebSocketHandler):
    pass

if __name__ == '__main__':
    tornado.options.parse_command_line()

    app = web.Application(handlers=[
        (r"/api/detection",DetectHandler),
        (r"/api/status",StatusHandler),
        (r"/api/comment",CommentHandler),
        (r"/api/like",LikeHandler),
        (r"/api/image",ImageHandler),
        (r"/api/404",FEFHandler)
        ], debug = False)
    http_server = tornado.httpserver.HTTPServer(app)
    http_server.listen(options.port)
    ioloop.IOLoop.instance().start()

    pool.close()
