<?php
namespace app\Tools\Parsers;

/**
 * Interface of the Strategy JSON Parser
 */
interface JSONParser
{

    /**
     * 
     * Converts JSON to Array depending of concrete Strategy
     * 
     * @param $obj_data json Object
     * 
     * @return array Object
     * 
     */
    public function Parse($obj_data): array;
}