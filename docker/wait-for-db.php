<?php
/**
 * Waits for MySQL to be ready using TCP + MySQL handshake verification.
 * PDO::ATTR_TIMEOUT does not reliably limit initial connection time in mysqlnd,
 * so we use stream_socket_client (respects timeout) and read MySQL's greeting packet.
 */
$host    = getenv('DB_HOST') ?: 'localhost';
$port    = (int)(getenv('DB_PORT') ?: 3306);
$retries = 30;

while ($retries-- > 0) {
    $fp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 3);
    if ($fp) {
        // MySQL sends its greeting immediately upon connection.
        // If we can read it (5+ bytes), MySQL is fully ready.
        stream_set_timeout($fp, 3);
        $greeting = @fread($fp, 16);
        fclose($fp);
        if (strlen($greeting) >= 5) {
            echo "[wait-db] MySQL is ready at {$host}:{$port}\n";
            exit(0);
        }
        echo "[wait-db] TCP open but MySQL not responding yet ({$retries} retries left)\n";
    } else {
        echo "[wait-db] {$host}:{$port} not reachable: {$errstr} ({$retries} retries left)\n";
    }
    sleep(2);
}

echo "[wait-db] Timed out waiting for {$host}:{$port}\n";
exit(1);
