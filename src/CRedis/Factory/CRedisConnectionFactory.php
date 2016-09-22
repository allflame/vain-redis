<?php
/**
 * Vain Framework
 *
 * PHP Version 7
 *
 * @package   vain-redis
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/allflame/vain-redis
 */

namespace Vain\Redis\CRedis\Factory;

use Vain\Connection\Exception\NoRequiredFieldException;
use Vain\Connection\Factory\AbstractConnectionFactory;

/**
 * Class CRedisConnectionFactory
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisConnectionFactory extends AbstractConnectionFactory
{
    /**
     * @param array $config
     *
     * @return string
     */
    protected function getPassword(array $config) : string
    {
        if (false === array_key_exists('password', $config)) {
            return '';
        }

        $password = $config['password'];

        if (false === array_key_exists('algo', $config)) {
            return $password;
        }

        return hash($config['algo'], $password);
    }

    /**
     * @param array $config
     *
     * @return array
     *
     * @throws NoRequiredFieldException
     */
    protected function getCredentials(array $config) : array
    {
        $requiredFields = ['host', 'port', 'db'];
        foreach ($requiredFields as $requiredField) {
            if (false === array_key_exists($requiredField, $config)) {
                throw new NoRequiredFieldException($this, $requiredField);
            }
        }

        return [$config['host'], (int)$config['port'], (int)$config['db'], $this->getPassword($config)];
    }

    /**
     * @inheritDoc
     */
    public function createConnection(array $config)
    {
        list ($host, $port, $db, $password) = $this->getCredentials($config);

        $redis = new \Redis();
        $redis->connect($host, $port);
        if ('' !== $password) {
            $redis->auth($password);
        }
        $redis->select($db);

        return $redis;
    }
}