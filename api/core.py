#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import tornadis
import tormysql
import bcrypt
import tornado

from urllib.parse import unquote
from tornado import gen, web



redis_client = tornadis.Client(host="45.76.176.44", port=6379, password="lq931110", autoconnect=True)
mysql_pool = tormysql.helpers.ConnectionPool(
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

class WWSHandler(web.RequestHandler):
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

        cursor = yield mysql_pool.execute(\
        "SELECT * FROM `users` WHERE `user_login` = '%s'"%(user_login))
        user = cursor.fetchall()
        if len(user) != 1:
            raise gen.Return(None)

        if bcrypt.hashpw(user[0][2].encode("utf-8"), user_pwd.encode("utf-8")) == user_pwd.encode("utf-8"):
            self._current_user = user[0][1]
            yield redis_client.call("SET", "user:" + user_login, self.request.remote_ip)
            yield redis_client.call("EXPIRE", "user:" + user_login, "1800") 
            

        raise gen.Return(self._current_user)

