#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from tornado import gen, web
from langdetect import detect


class DetectHandler(web.RequestHandler):
    def post(self):
        text = self.request.body
        self.write(detect(text.decode('UTF-8'))[:2])
