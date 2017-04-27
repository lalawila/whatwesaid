#!/usr/bin/env python3
# -*- coding: utf-8 -*-

from tornado import gen, web


class ImageHandler(web.RequestHandler):
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
