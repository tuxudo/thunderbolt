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
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => array('error' => 'Not authenticated')));
            return;
        }
        
        $usb = new Thunderbolt_model;
        $obj->view('json', array('msg' => $usb->get_thunderbolt_devices()));
     }
    
   /**
     * Retrieve data in json format
     *
     **/
    public function get_data($serial_number = '')
    {
        $obj = new View();

        if (! $this->authorized()) {
            $obj->view('json', array('msg' => 'Not authorized'));
            return;
        }
        
        $queryobj = new Thunderbolt_model();
        
        $sql = "SELECT name, vendor, current_speed, device_serial_number
                        FROM thunderbolt 
                        WHERE serial_number = '$serial_number'";
        
        $thunderbolt_tab = $queryobj->query($sql);

        $obj->view('json', array('msg' => current(array('msg' => $thunderbolt_tab)))); 
    }
		
} // END class Thunderbolt_controller
