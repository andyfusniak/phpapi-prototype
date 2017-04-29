<?php declare(strict_types=1);
namespace GreycatMedia\VmApi\Mapper;

use Monolog\Logger;
use GreycatMedia\VmApi\Mapper\Exception\ProfileNotFoundException;
use PDO;

class ProfileMapper
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchProfileById(int $profileId)
    {
        $sql = 'SELECT * FROM `profiles` WHERE profile_id = :profile_id';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(
            ':profile_id',
            $profileId,
            PDO::PARAM_INT
        );
        $statement->execute();
        $this->logger->debug(sprintf(
            'SQL Query executed %s',
            $sql
        ));
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setLogger(Logger $logger) : ProfileMapper
    {
        $this->logger = $logger;
        return $this;
    }
}
