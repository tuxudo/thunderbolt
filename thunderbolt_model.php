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
        $this->rs['timestamp'] = null;
        $this->rs['connected'] = null;
        $this->rs['switch_uid_key'] = ''; // Used only for unique matching for historical devices, do not make null

        // Add local config
        configAppendFile(__DIR__ . '/config.php');

        if ($serial) {
            $this->retrieve_record($serial);
        }

        $this->serial_number = $serial;
    }

    // ------------------------------------------------------------------------

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

        // If we didn't specify in the config that we like history then
        // we nuke any data we had with this computer's serial number
        if (! conf('thunderbolt_historical')) {
            $this->deleteWhere('serial_number=?', $this->serial_number);
        } else {

            // Clean up devices with a blank switch_uid_key
            $this->deleteWhere('serial_number=? AND switch_uid_key=?', array($this->serial_number, ''));

            // Set all Thunderbolt_historical devices to "0" for not connected, but only if we're keeping historical devices
            $sql = "UPDATE `thunderbolt` 
                    SET `connected` = '0'
                    WHERE `serial_number` = '$this->serial_number'";
            $this->query($sql);
        }

        // Timestamp added by the server
        $this->timestamp = time();

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);
        $myList = $parser->toArray();

        $typeList = array(
            'name' => '',
            'device_serial_number' => '',
            'vendor' => '',
            'current_speed' => '',
            'device_json' => '',
            'connected' => '',
            'switch_uid_key' => ''
        );

        foreach ($myList as $device) {
            // Check if we have a name
            if( ! array_key_exists("name", $device)){
                continue;
            }

            // Set that the device is currently connected
            $device['connected'] = 1;

            // Adjust names
            $device['name'] = str_replace(array('Service: '), array(' '), $device['name']);
            
            foreach ($typeList as $key => $value) {
                $this->rs[$key] = $value;
                if(array_key_exists($key, $device)) {
                    $this->rs[$key] = $device[$key];
                } else {
                    if ($key == "switch_uid_key"){
                        // Do not null switch_uid_key
                        $this->rs[$key] = "";
                    } else {
                        $this->rs[$key] = null;                    
                    }
                }
            }

            // If we are to not keep historical data, do a selective delete
            if (conf('thunderbolt_historical')) {
                // Selectively delete display by matching different aspects of the Thunderbolt device. Do NOT use Thunderbolt device serial number
                $this->deleteWhere('serial_number=? AND name=? AND switch_uid_key=? AND vendor=?', array($this->serial_number, $this->name, $this->switch_uid_key, $this->vendor));
            }

            // Save device
            $this->id = '';
            $this->save();
        }
    }
}
