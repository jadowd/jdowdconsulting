<?php
// Note: File was originally used in another personal project, will be tailoried to the Drupal Ham Radio Module project as development continues.
// Effective Date of Band Plan Data: February 23, 2007

define("HAM_EXTRA",     "5");
define("HAM_ADVANCED",  "4");
define("HAM_GENERAL",   "3");
define("HAM_TECH",      "2");
define("HAM_NOVICE",    "1");
define("HAM_NONE",      "0");

define("HAM_CW",		"1");
define("HAM_RTTY",  "2");
define("HAM_DATA",  "3");
define("HAM_MCW",   "4");
define("HAM_TEST",  "5");
define("HAM_PHONE", "6");
define("HAM_IMAGE", "7");
define("HAM_SSB",   "8");
define("HAM_USB",   "9");
define("HAM_DMF",   "10");

class band
{
	public $band_wavelength;
	
	public $band_notes;
	
	public $sub_bands;
	
	function __construct()
	{
		//nothing
	}
	
	function add_sub_band($in_start, $in_end, $in_privs, $in_modes, $in_limit)
	{
		$this->sub_bands[] = new sub_band($in_start, $in_end, $in_privs, $in_modes, $in_limit);
		
	}
	
	function get_sub_bands($license_type)
	{
		$retval = null;
		
		foreach($this->sub_bands as $sub)
		{
			if(in_array($license_type, $sub->privs))
			{
				$retval[] = $sub;
			}
		}
		
		return $retval;
	}
	
	function get_sub_band_by_freq($freq)
	{
		$retval = null;
		
		foreach($this->sub_bands as $sub)
		{
			if($freq >= $sub->start && $freq <= $sub->end)
			{
				$retval[] = $sub;
			}
		}
		
		return $retval;
	}
	
	function permission_to_use_band($license_type)
	{
		foreach($this->sub_bands as $sub)
		{
			if($sub->permission_to_use_sub_band($license_type))
			{
				return true;
			}
		}
		return false;
	}
	
	function get_usable_sub_bands($license_type)
	{
		$retval = null;
		
		foreach($this->sub_bands as $sub)
		{
			if($sub->permission_to_use_sub_band($license_type))
			{
				$retval[] = $sub;
			}
		}
		
		return $retval;
	}
	
	function get_usable_sub_bands2($freq, $license_type)
	{
		$retval = null;
		
		foreach($this->sub_bands as $sub)
		{
			if($sub->permission_to_use_sub_band($license_type) && $freq <= $sub->end && $freq >= $sub->start)
			{
				//echo "woot";
				$retval[] = $sub;
			}
		}
		
		return $retval;
	}
	
	function get_band_max()
	{
		$max = null;
		foreach($this->sub_bands as $sub)
		{
			if($sub->end > $max)
			{
				$max = $sub->end;
			}
		}
		return $max;
	}
	
	function get_band_min()
	{
		$min = null;
		foreach($this->sub_bands as $sub)
		{
			if($sub->start < $min || $min == null)
			{
				$min = $sub->start;
			}
		}
		return $min;
	}
	
	function get_freq_sub_bands($freq)
	{
		$retval = null;
		
		foreach($this->sub_bands as $sub)
		{
			if($freq <= $sub->end && $freq >= $sub->start)
			{
				//echo "woot";
				$retval[] = $sub;
			}
		}
		
		return $retval;
	}
}

class sub_band
{
	public $start;
	public $end;
	public $privs;
	public $modes;
	public $limit;
	
	function __construct($in_start, $in_end, $in_privs, $in_modes, $in_limit)
	{
		$this->start = $in_start;
		$this->end = $in_end;
		$this->privs = $in_privs;
		$this->modes = $in_modes;
		$this->limit = $in_limit;
	}
	
	function permission_to_use_sub_band($license_type)
	{
		if(in_array($license_type,$this->privs))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
}

class ham
{
	private $license_type;
	
	public $band_plan;
	
	function __construct()
	{
		$this->license_type = 0;
		
		$this->make_band_plan();
 		
	}
	
	function add_band($name)
	{
		$this->band_plan[$name] = new band();
	}
	
	function add_sub_band($in_start, $in_end, $in_privs, $in_modes, $band, $in_limit = 1500)
	{
		$this->band_plan[$band]->add_sub_band($in_start, $in_end, $in_privs, $in_modes, $in_limit);
	}
	
	function get_my_bands()
	{
		return $this->get_bands($this->get_license_type());
	}
	
	function get_bands($license_type)
	{
		$retval = null;
		
		foreach($this->band_plan as $key => $single_band)
		{
			if($single_band->permission_to_use_band($license_type))
			{
				$retval[$key] = $single_band->get_usable_sub_bands($license_type);
			}
		}
		
		return $retval;
	}
	
	function get_general_freq_info($freq)
	{
		$retval = null;
		
		foreach($this->band_plan as $key => $single_band)
		{
			//echo "checking band...<br>";
			if($freq <= $single_band->get_band_max() && $freq >= $single_band->get_band_min())
			{
				//echo "found band!<br>";
				$subbands = $single_band->get_freq_sub_bands();
				
				foreach($subbands as $sub)
				{
					$sb[] = array("");
				}
				
				$retval[$key] = "";
			}
		}
		
		return $retval;
	}
	
	function my_freq_info($freq)
	{
		return $this->freq_info($freq, $this->get_license_type());
	}
	
	function freq_info($freq, $license_type)
	{
		$retval = null;
		
		foreach($this->band_plan as $key => $single_band)
		{
			if($freq <= $single_band->get_band_max() && $freq >= $single_band->get_band_min())
			{
				$retval[$key] = $single_band->get_usable_sub_bands2($freq, $license_type);
			}
		}
		
		return $retval;
	}

	 function freq_band($freq)
	 {
	  $retval = null;
	
	  foreach($this->band_plan as $key => $single_band)
	  {
	    if($freq <= $single_band->get_band_max() && $freq >= $single_band->get_band_min())
	    {
	      $retval = $single_band->band_wavelength;
	    }
	  }
	
	  return $retval;
	 }

    function freq_modes($freq)
    {
            $retval = null;

            foreach($this->band_plan as $key => $single_band)
            {
               if($freq <= $single_band->get_band_max() && $freq >= $single_band->get_band_min())
               {
                            $retval = $single_band->get_usable_sub_bands2($freq, 5);
                            $retval = $retval[0]->modes;
                    }
            }

            return $retval;
    }


	
	function make_band_plan()
	{
		//make the band plan!
		
		//160 Meters
		$this->add_band("160 Meters");

		$this->band_plan['160 Meters']->band_wavelength = "160m";
		
		$this->band_plan['160 Meters']->band_notes = "Avoid interference to radiolocation operations from 1900 to 2000 kHz";
		
		$this->add_sub_band(1.800,2.000,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "160 Meters");
		
		
		//80 Meters
		$this->add_band("80 Meters");

		$this->band_plan['80 Meters']->band_wavelength = "80m";
		
		$this->add_sub_band(3.525,3.600,array(HAM_NOVICE, HAM_TECH), array(HAM_CW), "80 Meters", 200); //200 watt limit
		
		$this->add_sub_band(3.525,3.600,array(HAM_GENERAL, HAM_ADVANCED), array(HAM_CW, HAM_RTTY, HAM_DATA), "80 Meters");
		$this->add_sub_band(3.800,4.000,array(HAM_GENERAL), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "80 Meters");
		
		//$this->add_sub_band(3.525,3.600,array(HAM_ADVANCED, HAM_GENERAL), array(HAM_CW, HAM_RTTY, HAM_DATA), "80 Meters");
		$this->add_sub_band(3.700,4.000,array(HAM_ADVANCED), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "80 Meters");
		
		$this->add_sub_band(3.500,3.600,array(HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "80 Meters");
		$this->add_sub_band(3.600,4.000,array(HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "80 Meters");
		
		
		//60 Meters (power limit of 50 Watts)
		// must be centered on the center freq and no more than 2.8kHz bandwidth!
		//
		$this->add_band("60 Meters");

                $this->band_plan['60 Meters']->band_wavelength = "60m";
		
		$this->band_plan['60 Meters']->band_notes = "General, Advanced, and Amateur Extra licensees may use the following five channels on a secondary basis with a maximum effective radiated power of 50 W PEP relative to a half wave dipole. Only upper sideband suppressed carrier voice transmissions may be used. The frequencies are 5330.5, 5346.5, 5366.5, 5371.5 and 5403.5 kHz. The occupied bandwidth is limited to 2.8 kHz centered on 5332, 5348, 5368, 5373, and 5405 kHz respectively.";
		
		$this->add_sub_band(5.332 - 0.0015,5.332 + 0.0015,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_USB), "60 Meters",50);
		$this->add_sub_band(5.348 - 0.0015,5.348 + 0.0015,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_USB), "60 Meters",50);
		$this->add_sub_band(5.368 - 0.0015,5.368 + 0.0015,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_USB), "60 Meters",50);
		$this->add_sub_band(5.373 - 0.0015,5.373 + 0.0015,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_USB), "60 Meters",50);
		$this->add_sub_band(5.405 - 0.0015,5.405 + 0.0015,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_USB), "60 Meters",50);
		
		
		//40 Meters
		$this->add_band("40 Meters");
		
    $this->band_plan['40 Meters']->band_wavelength = "40m";

		$this->band_plan['40 Meters']->band_notes = "Phone and Image modes are permitted between 7075 and 7100 kHz for FCC licensed stations in ITU Regions 1 and 3 and by FCC licensed stations in ITU Region 2 West of 130 degrees West longitude or South of 20 degrees North latitude. See Sections 97.305(c) and 97.307(f)(11). Novice and Technician licensees outside ITU Region 2 may use CW only between 7025 and 7075 kHz. See Section 97.301(e). These exemptions do not apply to stations in the continental US.";
		
		$this->add_sub_band(7.025,7.125,array(HAM_NOVICE, HAM_TECH), array(HAM_CW), "40 Meters",200);
		
		$this->add_sub_band(7.025,7.125,array(HAM_GENERAL, HAM_ADVANCED), array(HAM_CW, HAM_RTTY, HAM_DATA), "40 Meters");
		$this->add_sub_band(7.175,7.300,array(HAM_GENERAL), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "40 Meters");
		
		//$this->add_sub_band(7.025,7.125,array(HAM_ADVANCED, HAM_GENERAL), array(HAM_CW, HAM_RTTY, HAM_DATA), "40 Meters");
		$this->add_sub_band(7.125,7.300,array(HAM_ADVANCED), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "40 Meters");
		
		$this->add_sub_band(7.000,7.125,array(HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "40 Meters");
		$this->add_sub_band(7.125,7.300,array(HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "40 Meters");
		
		
		//30 Meters (power limit of 200 watts PEP)
		$this->add_band("30 Meters");
		
    $this->band_plan['30 Meters']->band_wavelength = "30m";

		$this->band_plan['30 Meters']->band_notes = "Avoid interference to fixed services outside the US.";
		
		$this->add_sub_band(10.100,10.150,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "30 Meters",200);

		
		//20 Meters
		$this->add_band("20 Meters");
		
    $this->band_plan['20 Meters']->band_wavelength = "20m";

		$this->add_sub_band(14.025,14.150,array(HAM_GENERAL, HAM_ADVANCED), array(HAM_CW, HAM_RTTY, HAM_DATA), "20 Meters");
		$this->add_sub_band(14.225,14.350,array(HAM_GENERAL), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "20 Meters");
		
		//$this->add_sub_band(14.025,14.150,array(HAM_ADVANCED, HAM_GENERAL), array(HAM_CW, HAM_RTTY, HAM_DATA), "20 Meters");
		$this->add_sub_band(14.175,14.350,array(HAM_ADVANCED), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "20 Meters");
		
		$this->add_sub_band(14.000,14.150,array(HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "20 Meters");
		$this->add_sub_band(14.150,14.350,array(HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "20 Meters");
		
		
		//17 Meters
		$this->add_band("17 Meters");

    $this->band_plan['17 Meters']->band_wavelength = "17m";
		$this->add_sub_band(18.068,18.110,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "17 Meters");
		$this->add_sub_band(18.110,18.168,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "17 Meters");
		
		
		//15 Meters
		$this->add_band("15 Meters");

    $this->band_plan['15 Meters']->band_wavelength = "15m";
		
		$this->add_sub_band(21.025,21.200,array(HAM_NOVICE, HAM_TECH), array(HAM_CW), "15 Meters",200); //200 watt
		
		$this->add_sub_band(21.025,21.200,array(HAM_GENERAL, HAM_ADVANCED), array(HAM_CW, HAM_RTTY, HAM_DATA), "15 Meters");
		$this->add_sub_band(21.275,21.450,array(HAM_GENERAL), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "15 Meters");
		
		//$this->add_sub_band(7.025,7.125,array(HAM_ADVANCED, HAM_GENERAL), array(HAM_CW, HAM_RTTY, HAM_DATA), "15 Meters");
		$this->add_sub_band(21.225,21.450,array(HAM_ADVANCED), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "15 Meters");
		
		$this->add_sub_band(21.000,21.200,array(HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "15 Meters");
		$this->add_sub_band(21.200,21.450,array(HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "15 Meters");
		
		
		//12 Meters
		$this->add_band("12 Meters");

    $this->band_plan['12 Meters']->band_wavelength = "12m";

		$this->add_sub_band(24.890,24.930,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "12 Meters");
		$this->add_sub_band(24.930,24.990,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "12 Meters");
		
		//10 Meters
		$this->add_band("10 Meters");
		
    $this->band_plan['10 Meters']->band_wavelength = "10m";

		$this->add_sub_band(28.000,28.300,array(HAM_NOVICE, HAM_TECH), array(HAM_RTTY, HAM_DATA), "10 Meters",200); //200 watt limit
		$this->add_sub_band(28.300,28.500,array(HAM_NOVICE, HAM_TECH), array(HAM_CW, HAM_SSB), "10 Meters",200); //200 watt limit
		
		$this->add_sub_band(28.000,28.300,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA), "10 Meters");
		$this->add_sub_band(28.300,29.700,array(HAM_GENERAL, HAM_ADVANCED,HAM_EXTRA), array(HAM_CW, HAM_PHONE, HAM_IMAGE), "10 Meters");
		
		//6 Meters
		$this->add_band("6 Meters");

    $this->band_plan['6 Meters']->band_wavelength = "6m";

		$this->add_sub_band(50,50.1,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW), "6 Meters");
		$this->add_sub_band(50.1,54.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "6 Meters");

		//2 Meters
		$this->add_band("2 Meters");

    $this->band_plan['2 Meters']->band_wavelength = "2m";

		$this->add_sub_band(144.0,144.1,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW), "2 Meters");
		$this->add_sub_band(144.1,148.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "2 Meters");

		//1.25 Meters
		$this->add_band("1.25 Meters");
		
    $this->band_plan['1.25 Meters']->band_wavelength = "1.25m";

		//novice!
		$this->add_sub_band(222.0,225.0,array(HAM_NOVICE), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "1.25 Meters",25); //25 watts limit

		$this->add_sub_band(219.0,220.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_DMF), "1.25 Meters");
		$this->add_sub_band(222.0,225.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "1.25 Meters");
		
		//.7 Meters (power restrictions possibly)
		$this->add_band("70 Centimeters");
		
    $this->band_plan['70 Centimeters']->band_wavelength = "70cm";

		$this->band_plan['70 Centimeters']->band_notes = "Geographical and power restrictions may apply to all bands above 420 MHz. See The ARRL Operating Manual for information about your area";
		
		$this->add_sub_band(420.0,450.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "70 Centimeters");

		
		//.33 Meters
		$this->add_band("33 Centimeters");

    $this->band_plan['33 Centimeters']->band_wavelength = "33cm";
		
		$this->band_plan['33 Centimeters']->band_notes = "Geographical and power restrictions may apply to all bands above 420 MHz. See The ARRL Operating Manual for information about your area";
		
		$this->add_sub_band(902.0,928.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "33 Centimeters");

		
		//.23 Meters
		$this->add_band("23 Centimeters");

    $this->band_plan['23 Centimeters']->band_wavelength = "23cm";
		
		$this->band_plan['23 Centimeters']->band_notes = "Geographical and power restrictions may apply to all bands above 420 MHz. See The ARRL Operating Manual for information about your area";
		
		$this->add_sub_band(1270.0,1295.0,array(HAM_NOVICE), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "23 Centimeters",5); // 5 watts limit

		$this->add_sub_band(1240.0,1300.0,array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "23 Centimeters");

	
		//.23 Meters
		$this->add_band("Open Spaces");
		
    $this->band_plan['Open Spaces']->band_wavelength = "OPEN";

		$this->band_plan['Open Spaces']->band_notes = "All licensees except Novices are authorized all modes on these frequencies and above 275 GHz.";
		
		$this->add_sub_band(2300,2310, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(2390,2450, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(3300,3500, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(5650,5925, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		
		$this->add_sub_band(10000,10500, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(24000,24250, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(47000,47200, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(76000,81000, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(122250,123000, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(134000,141000, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		$this->add_sub_band(241000,250000, array(HAM_TECH, HAM_GENERAL, HAM_ADVANCED, HAM_EXTRA), array(HAM_CW, HAM_RTTY, HAM_DATA, HAM_PHONE, HAM_IMAGE), "Open Spaces");
		
	}
	
	//freq (mhz) to wavelength (meters)
	function freq_to_wlength($freq)
	{
		return 300/$freq;
	}
	
	//wavelength (meters) to freq (mhz)
	function wlength_to_freq($wlength)
	{
		return 300/$wlength;
	}
	
	function mhz_to_khz($freq)
	{
		return $freq * 1000;
	}
	
	function khz_to_mhz($freq)
	{
		return $freq / 1000;
	}
	
	function get_license_type()
	{
		return $this->license_type;
	}
	
	function display_freq($freq_in_mhz)
	{
		if($freq_in_mhz < 10)
		{
			// 0.1 MHz should be 100 kHz?
			return $freq_in_mhz * 1000 . " kHz";
		}
		elseif($freq_in_mhz >= 10000)
		{
			return number_format($freq_in_mhz / 1000, 3) . " GHz";
		}
		else 
		{
			return number_format($freq_in_mhz,3) . " MHz";
		}
	}
	
	function get_license_type_name($type)
	{		
		switch ($type)
		{
			case HAM_EXTRA:
			    return "Amateur Extra";
			    break;
			case HAM_ADVANCED:
			    return "Advanced";
			    break;
			case HAM_GENERAL:
			    return "General";
			    break;
			case HAM_TECH:
			    return "Technician";
			    break;
			case HAM_NOVICE:
			    return "Novice";
			    break;
			default:
				//NO LICENSE TYPE SET!
				return "No FCC License";
				break;
		}
	}
	
	function get_mode_name($type)
	{		
		switch ($type)
		{
			case HAM_CW:
			    return "CW";
			    break;
			case HAM_RTTY:
			    return "RTTY";
			    break;
			case HAM_DATA:
			    return "Data";
			    break;
			case HAM_MCW:
			    return "MCW";
			    break;
			case HAM_TEST:
			    return "Test";
			    break;
			case HAM_PHONE:
			    return "Phone";
			    break;
			case HAM_IMAGE:
			    return "Image";
			    break;
			case HAM_SSB:
			    return "SSB";
			    break;
			case HAM_USB:
			    return "USB";
			    break;
			case HAM_DMF:
			    return "Digital Message Forwarding";
			    break;
			default:
				return "[Invalid Mode]";
				break;
		}
	}
	
	function set_license_type($type)
	{
		$this->license_type = $type;
	}
}
?>
