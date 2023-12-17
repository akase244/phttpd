# phttpd

実行例

```
$ docker pull php:8.3-cli-alpine3.18
$ docker container run \
--rm \
-v .:/tmp \
-w /tmp \
-p 127.0.0.1:8000:8000 \
-it php:8.3-cli-alpine3.18 \
/bin/sh -c "apk update && apk add linux-headers && docker-php-ext-install sockets && php server.php"
```

public_html配下にindex.htmlを配置してブラウザから `http://localhost:8000/index.html` にアクセスする。<br>
または、ターミナルで `$ curl http://localhost:8000/index.html` を実行する。