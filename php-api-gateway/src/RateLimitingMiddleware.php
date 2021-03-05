<?php
namespace App;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use DateTime;
use DateInterval;
use PDO;
use Exception;
use PDOException;

class RateLimitingMiddleware
{
    const MAX_RATE = 50;
    const BAN_DURATION = 'PT1H';
    const HOST = '%DB_HOST%';
    const DBNAME = '%DB_NAME%';
    const USERNAME = '%DB_USERNAME%';
    const PASSWORD = '%DB_PASSWORD%';

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $currentDateTime = new DateTime();
        $banDuration = new DateInterval(RateLimitingMiddleware::BAN_DURATION);

        try {

            $db = new PDO(
                'mysql:host='.self::HOST.';dbname='.self::DBNAME.';charset=utf8',
                self::USERNAME,
                self::PASSWORD,
                [PDO::ERRMODE_EXCEPTION]
            );
            $query = $db->query('SELECT * FROM rate_limit');
            $data = $query->fetch();

            if (empty($data)) {
                throw new Exception('Rate limiting is not implemented');
            }

            if ($data['rate'] < RateLimitingMiddleware::MAX_RATE) {
                $query = $db->prepare('UPDATE rate_limit SET rate = rate + 1, last_connection = ?');
                $query->execute([$currentDateTime->getTimestamp()]);
                return $handler->handle($request);
            }

            $lastConnection = new DateTime("@{$data['last_connection']}");
            if ($lastConnection->add($banDuration)->getTimestamp() <= $currentDateTime->getTimestamp()) {
                $db->exec('UPDATE rate_limit SET rate = 0');
                return $handler->handle($request);
            }

        } catch (PDOException $exception) {
            throw new Exception('Database error. Please contact the website owner.');
        }

        throw new Exception(
            'The API rate limit has been reached. Please come back at '.$lastConnection->format('H:i'),
            StatusCodeInterface::STATUS_TOO_MANY_REQUESTS
        );
    }
}