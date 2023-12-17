<?php

$host = "0.0.0.0";
$port = 8000;

// ソケットを作成
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() 失敗: " . socket_strerror(socket_last_error()) . PHP_EOL;
    exit;
}

// ソケットにバインド
if (!socket_bind($socket, $host, $port)) {
    echo "socket_bind() 失敗: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
    exit;
}

// 接続待機
if (!socket_listen($socket, 5)) {
    echo "socket_listen() 失敗: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
    exit;
}

echo "サーバーが $host:$port で実行中...\n";

while (true) {
    // 接続を受け入れる
    $clientSocket = socket_accept($socket);
    if ($clientSocket === false) {
        echo "socket_accept() 失敗: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
        break;
    }

    // クライアントからのデータを読み込む
    $request = socket_read($clientSocket, 4096);
    echo "クライアントからのリクエスト: $request\n";

    // リクエストの内容からパスを抽出（ひとまずGETメソッドのみに対応する）
    preg_match("/GET (\/[^\s]*) /", $request, $matches);
    $path = isset($matches[1]) ? $matches[1] : '/';

    // ファイルの存在チェック
    $file = 'public_html' . DIRECTORY_SEPARATOR . $path;
    try {
        if (is_file($file)) {
            // レスポンスを作成
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $mimeTypes = [
                'html' => 'text/html',
                'txt'  => 'text/plain',
                'css'  => 'text/css',
                // 他の拡張子に対するMIMEタイプを追加
            ];

            // 拡張子に応じてMIMEタイプを確定する
            $contentType = isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream';
            // ファイルが存在する場合はその内容を返す
            $content = file_get_contents($file);
            $response = "HTTP/1.1 200 OK\r\nContent-Type: $contentType\r\n\r\n$content";
        } else {
            // ファイルが存在しない場合は404エラーを返す
            $response = "HTTP/1.1 404 Not Found\r\nContent-Type: text/plain\r\n\r\n404 Not Found";
        }
    } catch (Exception $e) {
        // 予期せぬエラーが発生した場合は500エラーを返す
        $response = "HTTP/1.1 500 Internal Server Error\r\nContent-Type: text/plain\r\n\r\n500 Internal Server Error";
    }

    // レスポンスをクライアントに送信
    socket_write($clientSocket, $response, strlen($response));

    // ソケットを閉じる
    socket_close($clientSocket);
}
