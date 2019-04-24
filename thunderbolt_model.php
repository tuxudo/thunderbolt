<?php

use CFPropertyList\CFPropertyList;

class Thunderbolt_model extends \Model {

	function __construct($serial='')
	{
		parent::__construct('id', 'thunderbolt'); // Primary key, tablename
		$this->rs['id'] = '';
		$this->rs['serial_number'] = $serial;
		$this->rs['name'] = '';
		$this->rs['device_serial_number'] = '';
		$this->rs['vendor'] = '';
		$this->rs['current_speed'] = '';
		$this->rs['device_json'] = '';

        if ($serial) {
            $this->retrieve_record($serial);
        }

		$this->serial_number = $serial;
	}
	
	// ------------------------------------------------------------------------
   
     /**
     * Get Thunderbolt device names for widget
     *
     **/
     public function get_thunderbolt_devices()
     {
        $out = array();
        $sql = "SELECT COUNT(CASE WHEN name <> '' AND name IS NOT NULL THEN 1 END) AS count, name 
                FROM thunderbolt
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY name
                ORDER BY count DESC";
        
        foreach ($this->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->name = $obj->name ? $obj->name : 'Unknown';
                $out[] = $obj;
            }
        }
        return $out;
     }
    
	/**
	 * Process data sent by postflight
	 *
	 * @param string data
	 * @author tuxudo
	 **/
	function process($plist)
	{
		// Check if we have data
		if ( ! $plist){
			throw new Exception("Error Processing Request: No property list found", 1);
		}
		
		// Delete previous set        
		$this->deleteWhere('serial_number=?', $this->serial_number);

		$parser = new CFPropertyList();
		$parser->parse($plist, CFPropertyList::FORMAT_XML);
		$myList = $parser->toArray();
        		
		$typeList = array(
			'name' => '',
			'device_serial_number' => '',
			'vendor' => '',
			'current_speed' => '',
			'device_json' => ''
		);
		
		foreach ($myList as $device) {
			// Check if we have a name
			if( ! array_key_exists("name", $device)){
				continue;
			}

			// Adjust names
			$device['name'] = str_replace(array('Service: '), array(' '), $device['name']);
            
			foreach ($typeList as $key => $value) {
				$this->rs[$key] = $value;
				if(array_key_exists($key, $device))
				{
					$this->rs[$key] = $device[$key];
				} else {
                    $this->rs[$key] = null;
                }
			}

			// Save device
			$this->id = '';
			$this->save();
		}
	}
}
