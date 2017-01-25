<?php
namespace Gm\Mapper;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Aws\DynamoDb\Marshaler;
use Gm\Mapper\Exception\ProfileNotFoundException;

class ProfileMapper
{
    /**
     * @var DynamoDbClient
     */
    protected $dynamoDbClient;

    /**
     * @var Marshaler
     */
    protected $marshaler;

    public function __construct(DynamoDbClient $dynamoDbClient)
    {
        $this->dynamoDbClient = $dynamoDbClient;
    }

    public function fetchProfileById(int $id)
    {
        $marshaler = $this->getMarshaler();

        $params = [
            'TableName' => 'dd_profiles',
            'Key' => $marshaler->marshalItem([
                'id' => $id
            ])
        ];

        try {
            $result = $this->dynamoDbClient->getItem($params);
        } catch (DynamoDbException $e) {
            throw $e;
        };

        if (!isset($result['Item'])) {
            throw new ProfileNotFoundException(sprintf(
                'Profile with id %s not found',
                $id
            ));
        }
        return $marshaler->unmarshalItem($result['Item']);
    }

    private function getMarshaler()
    {
        if (null === $this->marshaler) {
            return new Marshaler();
        }
        return $this->marshaler;
    }
}
