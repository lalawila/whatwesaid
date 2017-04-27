#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from tornado import gen
from core import Op, WWSHandler

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

