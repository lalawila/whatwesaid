#!/usr/bin/env python3
# -*- coding: utf-8 -*-


import tornado.web
import tornado.websocket
import tornado.httpserver
import tornado.ioloop
import tornado.options
import base64
import time
import os
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

class CommentHandler(tornado.web.RequestHandler):
    def post(self):
        pass

class ImageSave(tornado.web.RequestHandler):
    def post(self):
        upload_path=os.path.join('content', 'image')
        file_metas=self.request.files['inputfile']
        for meta in file_metas:
            name = str(base64.urlsafe_b64encode(str(time.time()).encode()))
            name = name[2:-1] + '.'
            name += meta['content_type'][6:]
            filepath = os.path.join(upload_path, name)
            with open(filepath,'wb') as file:
                file.write(meta['body'])
            self.write(filepath)
            print(filepath)

if __name__ == '__main__':
    tornado.options.parse_command_line()

    app = tornado.web.Application(handlers=[
        (r"/detect.py",Detect_lang),
        (r"/status.py",StatusHandler),
        (r"/comment.py",CommentHandler),
        (r"/image.py",ImageSave)
        ])
    http_server = tornado.httpserver.HTTPServer(app)
    http_server.listen(options.port)
    tornado.ioloop.IOLoop.instance().start()
