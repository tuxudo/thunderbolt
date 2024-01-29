<?php 

/**
 * Thunderbolt module class
 *
 * @package munkireport
 * @author tuxudo
 **/
class Thunderbolt_controller extends Module_controller
{
    
    /*** Protect methods with auth! ****/
    function __construct()
    {
        // Store module path
        $this->module_path = dirname(__FILE__);
    }

    /**
     * Default method
     * @author avb
     *
     **/
    function index()
    {
        echo "You've loaded the thunderbolt module!";
    }

   /**
     * Get Thunderbolt device names for widget
     *
     * @return void
     * @author tuxudo
     **/
     public function get_thunderbolt_devices()
     {
        $sql = "SELECT COUNT(CASE WHEN name <> '' AND name IS NOT NULL THEN 1 END) AS count, name 
                FROM thunderbolt
                LEFT JOIN reportdata USING (serial_number)
                ".get_machine_group_filter()."
                GROUP BY name
                ORDER BY count DESC";

        $out = array();
        $queryobj = new Thunderbolt_model;
        foreach ($queryobj->query($sql) as $obj) {
            if ("$obj->count" !== "0") {
                $obj->type = $obj->type ? $obj->type : 'Unknown';
                $out[] = $obj;
            }
        }

        jsonView($out);
     }

   /**
     * Retrieve data in json format
     *
     **/
    public function get_data($serial_number = '')
    {
        // Remove non-serial number characters
        $serial_number = preg_replace("/[^A-Za-z0-9_\-]]/", '', $serial_number);

        $sql = "SELECT `name`, `connected`, `vendor`, `current_speed`, `device_serial_number`, `timestamp`
                        FROM thunderbolt 
                        WHERE serial_number = '$serial_number'";

        $obj = new View();        
        $queryobj = new Thunderbolt_model();
        $thunderbolt_tab = $queryobj->query($sql);
        $obj->view('json', array('msg' => current(array('msg' => $thunderbolt_tab)))); 
    }
} // End class Thunderbolt_controller
