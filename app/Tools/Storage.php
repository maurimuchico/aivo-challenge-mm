<?php
namespace app\Tools;

/**
 * Provides encrypted data persistance in filesystem
 * 
 * Uses JSON representation to store keys and values
 */
class Storage
{
    const FILE = '../data/db.dat';
    const ENCRYPTION_METHOD = 'aes256';
    const ENCRYPTION_PASS = 'aivo-challenge-mm';
    const ENCRYPTION_IV = '3204132203463278';

    /**
     * 
     * Stores data in the $key entry
     * 
     * @param $key where to store the data
     * @param $data to store
     * 
     */
    public static function SaveDataByKey($key, $data)
    {
        $plain_data = self::GetAllData();
        if ($plain_data) {
            $plain_data->$key = $data;
            $plain_data = json_encode($plain_data);
        } else {
            $plain_data = json_encode([
                $key => $data
            ]);
        }
        $encrypted_data = openssl_encrypt($plain_data, self::ENCRYPTION_METHOD, self::ENCRYPTION_PASS, 0, self::ENCRYPTION_IV);
        file_put_contents(self::FILE, $encrypted_data);
    }

    /**
     * 
     * Retrieves data in the $key entry
     * 
     * @param $key used to retrieve the data
     * 
     */
    public static function GetDataByKey($key)
    {
        $keys_data = self::GetAllData();
        if ($keys_data) {
            if (property_exists($keys_data, $key)) {
                return $keys_data->$key;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /*
     * Reads the entire file to manipulate its keys
     */
    private static function GetAllData()
    {
        if (file_exists(self::FILE)) {
            $encrypted_data = file_get_contents(self::FILE);
            $plain_data = openssl_decrypt($encrypted_data, self::ENCRYPTION_METHOD, self::ENCRYPTION_PASS, 0, self::ENCRYPTION_IV);
            return json_decode($plain_data);
        } else {
            return null;
        }
    }
}