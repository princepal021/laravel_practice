<?php

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Countries;
use App\Cities;
use App\States;
use Illuminate\Support\Facades\Crypt;

function siteUrl()
{
    //return 'http://192.168.1.209:8080';
    return url('/');
}
function adminPublicPath()
{
    return asset('/public/admin');
    // return asset('/admin');
}
function publicPath()
{
    // return asset('');
    return asset('/public');
}
function publicbasePath()
{
    // return '';
    return 'public/';
}
function pagination()
{
    return 25;
}
function priceFormat($number){
    return '$ '.number_format($number, 2);
}
function itemShowPrice($menuItem)
{
    /*if ($menuItem->item_is == 'Attributes') {
        return \App\MenuItemAttributes::join('menu_attributes','menu_attributes.menu_attr_id','menu_item_attributes.menu_attr_id')
            ->where('menu_item_id', $menuItem->menu_item_id)
            ->where('menu_attributes.attr_main_choice', 1)
            ->select('menu_item_attributes.attr_name as item_attr_name','menu_item_attributes.attr_price', 'menu_item_attributes.item_attr_id','menu_attributes.attr_name as menu_attr_name','menu_attributes.attr_type')
            ->orderBy('attr_price', 'ASC')
            ->get()->pluck('attr_price')->first();
    }*/
    $delivery_pickup_address = Session::get('delivery_pickup_address');
    $slot = (isset($delivery_pickup_address['slot'])?$delivery_pickup_address['slot']:'');
    $order_type = (isset($delivery_pickup_address['order_type'])?$delivery_pickup_address['order_type']:'');
    $item_for = json_decode($menuItem->item_for);
    $extraPrice = 0;
    if ($order_type == 'Pickup') {
        $extraPrice = (isset($item_for->$slot->pickup_price)?$item_for->$slot->pickup_price:0);
    } else {
        $extraPrice = (isset($item_for->$slot->delivery_price)?$item_for->$slot->delivery_price:0);
    }
    $price = $menuItem->item_price;
    if ($menuItem->item_sale_price) {
        $price = $menuItem->item_sale_price;
    }
    $discountPrice = $price;
    if ($menuItem->item_discount_start <= date('Y-m-d') && $menuItem->item_discount_end >= date('Y-m-d')) {
        $discount = ($price * $menuItem->item_discount / 100);
        $discountPrice = $price - $discount;
    }
    return $discountPrice+$extraPrice;
}
function adminBasePath()
{
    return 'admin';
}
function adminEmail()
{
    $admin_settings = getThemeOptions('admin_settings');
    return (isset($admin_settings['admin_email'])?$admin_settings['admin_email']:'info@www.infiway.ae');
}
function CleanHtml($html = null)
{
    return preg_replace(
        array(
            '/ {2,}/',
            '/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
        ),
        array(
            ' ',
            ''
        ),
        $html
    );
}

function SendSMS($mobileNumber, $message)
{
	return;
}

function maybe_decode( $original ) {
	if ( is_serialized( $original ) )
	    return @unserialize( $original );
	return $original;
}
function is_serialized( $data, $strict = true ) {
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' == $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	}
	else
	{
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		if ( false === $semicolon && false === $brace )
			return false;
		if ( false !== $semicolon && $semicolon < 3 )
			return false;
		if ( false !== $brace && $brace < 4 )
			return false;
	}
	$token = $data[0];
	switch ( $token ) {
		case 's' :
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
				    return false;
				}
			}
			elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
		case 'a' :
		case 'O' :
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b' :
		case 'i' :
		case 'd' :
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}
	return false;
}

function maybe_encode( $data ) {
	if ( is_array( $data ) || is_object( $data ) )
	     return serialize( $data );
	if ( is_serialized( $data, false ) )
		return serialize( $data );
    return $data;
}

function fileuploadmultiple($request)
{
    $files = $request->file('attachments');
    $uploaded_file = [];
    foreach($files as $file) {
        $destinationPath = publicbasePath().'/images/uploads/'.date('Y').'/'.date('M');
        $filename = str_replace(array(' ','-','`',','),'_',time().'_'.$file->getClientOriginalName());
        $upload_success = $file->move($destinationPath, $filename);
        $uploaded_file[] = 'images/uploads/'.date('Y').'/'.date('M').'/'.$filename;
    }
    return $uploaded_file;
}
function fileupload($request){
    $file = $request->file('image');
    $destinationPath = publicbasePath().'/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'images/uploads/'.date('Y').'/'.date('M').'/'.$filename;
    return $uploaded_file;
}
function fileuploadExtra($request, $key){
    $file = $request->file($key);
    $destinationPath = publicbasePath().'/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'images/uploads/'.date('Y').'/'.date('M').'/'.$filename;
    return $uploaded_file;
}
function fileuploadArray($file){
    $destinationPath = publicbasePath().'/images/uploads/'.date('Y').'/'.date('M');
    $filename = time().'_'.$file->getClientOriginalName();
    $upload_success = $file->move($destinationPath, $filename);
    $uploaded_file = 'images/uploads/'.date('Y').'/'.date('M').'/'.$filename;
    return $uploaded_file;
}
function randomPassword() {
    return mt_rand(100000, 999999);
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
function encryptID($value = null)
{
    return Crypt::encryptString($value);
}
function decryptID($value = null)
{
    try {
        return Crypt::decryptString($value);
    } catch (DecryptException $e) {
        return ;
    }
}
function getApiCurrentUser()
{
	if (empty(Request()->header('token'))) {
		return new \App\User();
	}
    $token = Request()->header('token');
    $user_id = decryptID($token);
	$user = getUser($user_id);
    if ($user) {
        return $user;
    }
    return new \App\User();
}
function getCurrentUser()
{
    $user = Auth::user();
    if (empty($user)) {
        return new \App\User();
    }
    return $user;
}
function getCurrentUserByKey($key)
{
	$user = getCurrentUser();
	if (!empty($key)) {
		return isset($user->$key)?$user->$key:0;
	}
	return $user;
}
function getUser($user_id)
{
    return \App\User::find($user_id);
}
function createUuid($name = 'vendorP')
{
	return Uuid::generate(5, $name, Uuid::NS_DNS);
}
function getCountry($country_name = null)
{
    if (!empty($country_name)) {
        return Countries::where('name',$country_name)->get()->pluck('name')->first();
    }
    return Countries::get();
}
function getState($country_name = null, $state_id = null)
{

    if (!empty($state_id)) {
        return States::where('id', $state_id)->get()->pluck('name')->first();
    }
	return States::where('country_id', Countries::where('name',$country_name)->get()->pluck('id')->first())->get();
}

function getStateCity($state_name = null, $city_id = null)
{
    if (!empty($city_id)) {
        return Cities::where('id', $city_id)->get()->pluck('name')->first();
    }
	return Cities::where('state_id', States::where('name', $state_name)->get()->pluck('id')->first())->get();
}
function getPercantageAmount($amount, $percent)
{
    return $amount/100*$percent;
}
function getDuration($date)
{
  $time = '';
  $t1 = \Carbon\Carbon::parse($date);
  $t2 = \Carbon\Carbon::parse();
  $diff = $t1->diff($t2);
  if ($diff->format('%y')!=0) {
    $time .= $diff->format('%y')." Year ";
  }
  if ($diff->format('%m')!=0) {
    $time .= $diff->format('%m')." Month ";
  }
  if ($diff->format('%d') && $diff->format('%m')==0) {
    $time .= $diff->format('%d')." Days ";
  }
  if ($diff->format('%h')!=0 && $diff->format('%m')==0) {
    $time .= $diff->format('%h')." Hours ";
  }
  if ($diff->format('%i')!=0 && $diff->format('%d')==0) {
    $time .= $diff->format('%i')." Minutes ";
  }
  if ($diff->format('%s')!=0 && $diff->format('%h')==0) {
    $time .= $diff->format('%s')." Seconds ";
  }
  return $time; 
}
function weekOfMonth($currentMonth)
{
    $stdate = $currentMonth.'-01';
    $enddate = $currentMonth.'-31'; //get end date of month
    $begin = new \DateTime('first day of ' . $stdate);
    $end = new \DateTime('last day of ' . $enddate);
    $interval = new \DateInterval('P1W');
    $daterange = new \DatePeriod($begin, $interval, $end);

    $dates = array();
    foreach($daterange as $key => $date) {
        $check = ($date->format('W') != $end->modify('last day of this month')->format('W')) ? '+6 days' : 'last day of this week';
        $dates[$key+1] = array(
            'start' => $date->format('Y-m-d'),
            'end' => ($date->modify($check)->format('Y-m-d')),
        );
        if ($dates[$key+1]['end']>date('Y-m-d', strtotime($enddate))) {
              $dates[$key+1]['end'] = date('Y-m-d', strtotime($enddate));
        }
    }
    return $dates;
}

function getLatLong($address = null)
{
	$latLong = [];
	$latLong['lattitude'] = '';
	$latLong['longitude'] = '';
	if (!empty($address)) {
		$address = str_replace(" ", "+", $address);
		$json = file_get_contents("https://maps.google.com/maps/api/geocode/json?key=AIzaSyCjEHaWgv-lmblYJ-m0fp3lwfrWrgzQEPE&address=".urlencode($address)."&sensor=false");
		$json = json_decode($json);
		if ($json->status == 'OK') {
			$latLong['lattitude'] = $json->results[0]->geometry->location->lat;
			$latLong['longitude'] = $json->results[0]->geometry->location->lng;
		}
	}
	return $latLong;
}
function address($user)
{
	$address = [];
	if (isset($user->address) && !empty($user->address)) {
		$address[] = $user->address;
	}
	if (isset($user->city) && !empty($user->city)) {
		$address[] = $user->city;
	}
	if (isset($user->state) && !empty($user->state)) {
		$address[] = $user->state;
	}
	if (isset($user->country) && !empty($user->country)) {
		$address[] = $user->country;
	}
	return implode(',', $address);
}
function bindAddress($user)
{
	$address = [];
	if (isset($user->address) && !empty($user->address)) {
		$address[] = $user->address;
	}
	if (isset($user->city) && !empty($user->city)) {
		$address[] = $user->city;
	}
	if (isset($user->state) && !empty($user->state)) {
		$address[] = $user->state;
	}
	if (isset($user->country) && !empty($user->country)) {
		$address[] = $user->country;
	}
	$address = implode(' ', $address);
	echo str_replace(" ", "+", $address);
}
function ip_info($purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    $ip = $_SERVER['REMOTE_ADDR'];
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));

        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}
function getSinglePost($post_id)
{
    $post = \App\Posts::leftJoin('posts as getImage','getImage.post_id','posts.guid')
        ->select('posts.*','getImage.media as post_image')
        ->where('posts.post_status', 'publish')
        ->where('posts.post_id', $post_id)
        ->where('posts.post_lng', defaultLanguage())
        ->first();

    if($post)
    {
        $postTypes = getPostType($post->post_type);
        $termRelations = \App\TermRelations::where('object_id', $post->post_id)->select('term_id');
        if(!empty($postTypes['taxonomy']))
        {
            $termsSelected = [];
            foreach ($postTypes['taxonomy'] as $key => $value) {
                $termsSelected[$key] = \App\Terms::whereIn('term_id', $termRelations)->where('term_group', $key)->get();
            }
            $post->category = $termsSelected;
        }
        $post->extraFields = getPostMeta($post->post_id);
        if ($post->post_type == 'service') {
            $post->hourSlots = hourSlots();
        }
    } else {
        return new \App\Posts();
    }

    return $post;
}
function phoneOtpSendVarification($phone='')
{
    if (empty($phone)) {
        echo 'empty';
        die;
    }
    \App\PhoneOtpVerification::where('phone', $phone)->where('otp_for', 'phone_number')->where('otp_status', 0)->delete();
    $otp_code = rand(1, 1000000);
    \App\PhoneOtpVerification::insertGetId([
        'phone' => $phone,
        'otp_code' => $otp_code,
        'time' => new DateTime,
        'otp_for' => 'phone_number',
        'otp_status' => 0,
        'created_at' => new DateTime,
        'updated_at' => new DateTime
    ]);
    $message = 'Your Otp for phone verification code is '.$otp_code;

    SendSMS($phone, $message);
    return $otp_code;
}

function calculateDaysAccTime($days,$start_time,$end_time)
{
    $start_time_h = strtotime($start_time);
    $end_time_h = strtotime($end_time);
    if($end_time_h < $start_time_h) {
        $end_time_h += 24 * 60 * 60;
    }
    $total_min = ($end_time_h - $start_time_h) / 60;
    if($total_min < 300)
    {
        $days = $days/2;
    }
    return $days;
}

function postTypes()
{
    $roles = userRoles();
    return [
        'post' => [
            'title' => 'Post',
            'icon' => 'ti-layout-list-post',
            'roles' => [$roles[0]],
            'showMenu' => true,
            'multilng' => false,
            'support' => ['content','excerpt','seo','featured'],
            'templateOption' => [
                'PostDefault' => 'Default Template',
            ],
            'taxonomy' => [
                'category' => [
                    'title' => 'Category',
                    'showMenu' => false,
                    'showPost' =>[],
                    'hasVariations' => false
                ],
                'tag' => [
                    'title' => 'Tag',
                    'showMenu' => false,
                    'showPost' =>[],
                    'hasVariations' => false
                ],
            ]
        ],
        'page' => [
            'title' => 'Page',
            'icon' => 'ti-layout-grid2-alt',
            'roles' => [$roles[0]],
            'showMenu' => true,
            'multilng' => false,
            'support' => ['content','excerpt','seo','featured'],
            'templateOption' => [
                'PageDefault' => 'Default Template',
                'AboutUs' => 'About Us Template',
                'ContactUs' => 'Contact Us Template',
                'Blog' => 'Blog Template',
                'Menu' => 'Menu Template',
                'Reservation' => 'Reservation Template',
                'Specialities' => 'Specialities Template',
                'Cart' => 'Cart Template',
                'Checkout' => 'Checkout Template',
                'ThankYou' => 'ThankYou Template',
                'Cancel' => 'Cancel Template',
                'MyAccount' => 'My Account',
                'MyOrder' => 'My Order'
            ],
            'taxonomy' => []
        ],
        'slider' => [
            'title' => 'Slider',
            'icon' => 'ti-layout-media-right-alt',
            'roles' => [$roles[0]],
            'showMenu' => false,
            'multilng' => false,
            'support' => ['content','featured'],
            'templateOption' => [
                'sliderDefault' => 'Default Template',
            ],
            'taxonomy' => []
        ],
        'testimonials' => [
            'title' => 'Testimonials',
            'icon' => 'ti-comments',
            'roles' => [$roles[0]],
            'showMenu' => false,
            'multilng' => false,
            'support' => ['content','featured'],
            'templateOption' => [
                'testimonialsDefault' => 'Default Template',
            ],
            'taxonomy' => []
        ],
        'our_delicious_specialties' => [
            'title' => 'Our Delicious Specialties',
            'icon' => 'ti-flickr-alt',
            'roles' => [$roles[0]],
            'showMenu' => false,
            'multilng' => false,
            'support' => ['content','featured'],
            'templateOption' => [
                'deliciousSpecialtiesDefault' => 'Default Template',
            ],
            'taxonomy' => []
        ],
        /*'products' => [
            'title' => 'Products',
            'icon' => 'ti-pin-alt',
            'roles' => [$roles[0]],
            'showMenu' => false,
            'multilng' => false,
            'support' => ['content','excerpt','seo','featured'],
            'templateOption' => [
                'productsDefault' => 'Default Template',
            ],
            'taxonomy' => [
                'product_category' => [
                    'title' => 'Product Category',
                    'showMenu' => false,
                    'showPost' =>[],
                    'hasVariations' => false
                ],
                'attribute_category' => [
                    'title' => 'Attributes',
                    'showMenu' => false,
                    'showPost' =>[],
                    'hasVariations' => true
                ],
            ]
        ]*/
    ];
}
function timeSlots(){
    return [
        '11:00:00' => '11:00 AM',
        '11:15:00' => '11:15 AM',
        '11:30:00' => '11:30 AM',
        '11:45:00' => '11:45 AM',
        '12:00:00' => '12:00 PM',
        '12:15:00' => '12:15 PM',
        '12:30:00' => '12:30 PM',
        '12:45:00' => '12:45 PM',
        '13:00:00' => '01:00 PM',
        '13:15:00' => '01:15 PM',
        '13:30:00' => '01:30 PM',
        '13:45:00' => '01:45 PM',
        '14:00:00' => '02:00 PM',
        '14:15:00' => '02:15 PM',
        '14:30:00' => '02:30 PM',
        '14:45:00' => '02:45 PM',
        '15:00:00' => '03:00 PM',
        '15:15:00' => '03:15 PM',
        '15:30:00' => '03:30 PM',
        '15:45:00' => '03:45 PM',
        '16:00:00' => '04:00 PM',
        '16:15:00' => '04:15 PM',
        '16:30:00' => '04:30 PM',
        '16:45:00' => '04:45 PM',
        '17:00:00' => '05:00 PM',
        '17:15:00' => '05:15 PM',
        '17:30:00' => '05:30 PM',
        '17:45:00' => '05:45 PM',
        '18:00:00' => '06:00 PM',
        '18:15:00' => '06:15 PM',
        '18:30:00' => '06:30 PM',
        '18:45:00' => '06:45 PM',
        '19:00:00' => '07:00 PM',
        '19:15:00' => '07:15 PM',
        '19:30:00' => '07:30 PM',
        '19:45:00' => '07:45 PM',
        '20:00:00' => '08:00 PM',
        '20:15:00' => '08:15 PM',
        '20:30:00' => '08:30 PM',
        '20:45:00' => '08:45 PM',
        '21:00:00' => '09:00 PM',
        '21:15:00' => '09:15 PM',
        '21:30:00' => '09:30 PM',
        '21:45:00' => '09:45 PM',
        '22:00:00' => '10:00 PM',
        '22:15:00' => '10:15 PM',
        '22:30:00' => '10:30 PM',
        '22:45:00' => '10:45 PM',
        '23:00:00' => '11:00 PM',
        '23:15:00' => '11:15 PM',
        '23:30:00' => '11:30 PM',
        '23:45:00' => '11:45 PM',
    ];
}
function defaultLanguage()
{
    $post_lng = '';
    $getSupportLNG = getSupportLNG();
    foreach ($getSupportLNG as $key => $value) {
        if (isset($getSupportLNG[$key]['type']) && $getSupportLNG[$key]['type'] == 'default') {
            $post_lng = $key;
        }
    }
    return $post_lng;
}
function userRoles()
{
        return [
            'Admin','DeliveryBoy','StoreAdmin','StoreEmployee','Customer'
        ];
}
function adminSideBarMenus(){
    $roles = userRoles();
    return [
        'stores.update' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'siteMap' => [
            'title' => 'Site Map',
            'route' => 'javascript:void(0)',//route('siteMap.index'),
            'icon' => 'ti-layout-media-center',
            'roles' => [$roles[0]],
            'child' => []
        ],
        'users' => [
            'title' => 'Users',
            'route' => 'javascript:void(0)',
            'roles' => [$roles[0]],
            'icon' => 'ti-user',
            'child' => [
                [
                    'title' => 'View',
                    'route' => route('users.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Create',
                    'route' => route('users.create'),
                    'icon' => 'ti-angle-right',
                ]
            ]
        ],
        'comment' => [
            'title' => 'Comments',
            'route' => route('comment.index'),
            'icon' => 'ti-comment-alt',
            'roles' => [$roles[0]],
            'child' => []
        ],
        'media' => [
            'title' => 'Media',
            'route' => route('media.index'),
            'icon' => 'ti-gallery',
            'roles' => $roles,
            'child' => []
        ],
        'feedback' => [
            'title' => 'FeedBack',
            'route' => route('feedback.get'),
            'icon' => 'ti-comment',
            'roles' => [$roles[0]],
            'child' => []
        ],
        'orders' => [
            'title' => 'Orders',
            'route' => route('orders.index'),
            'icon' => 'ti-world',
            'roles' => [$roles[0], $roles[2]],
            'child' => []
        ],
        'menus' => [
            'title' => 'Menus',
            'route' => route('menus'),
            'icon' => 'ti-menu-alt',
            'roles' => [$roles[0]],
            'child' => [
            ]
        ],
        'themes' => [
            'title' => 'Theme Settings',
            'route' => route('themes.index'),
            'icon' => 'ti-settings',
            'roles' => [$roles[0]],
            'child' => [
            ]
        ],
        'stores' => [
            'title' => 'Stores',
            'route' => route('stores.index'),
            'icon' => 'ti-blackboard',
            'roles' => [$roles[0]],
            'child' => [
            ]
        ],
        'store' => [
            'title' => 'Store',
            'route' => 'javascript:void(0)',
            'icon' => 'ti-blackboard',
            'roles' => [$roles[2], $roles[3]],
            'child' => [
                [
                    'title' => 'Store',
                    'route' => route('store.showStore').'?tab=StoreInfo',
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Store Users',
                    'route' => route('storeUsers.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Store Holidays',
                    'route' => route('storeHolidays.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Store SurgeCharges',
                    'route' => route('storesSurgeCharges.index'),
                    'icon' => 'ti-angle-right',
                ]
            ]
        ],
        'menuItem' => [
            'title' => 'Menus',
            'route' => 'javascript:void(0)',
            'icon' => 'ti-agenda',
            'roles' => [$roles[2], $roles[3]],
            'child' => [
                [
                    'title' => 'Over View',
                    'route' => route('menuItem.sortItemCat'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Menu Items',
                    'route' => route('menuItem.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Menu Attribute',
                    'route' => route('menuAttribute.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Menu Item Category',
                    'route' => route('menuItemCategory.index'),
                    'icon' => 'ti-angle-right'
                ],
                [
                    'title' => 'Menu Attribute Size',
                    'route' => route('menuAttributeSize.index'),
                    'icon' => 'ti-angle-right',
                ],
                [
                    'title' => 'Menu Banners',
                    'route' => route('menuItemBanner.index'),
                    'icon' => 'ti-angle-right',
                ]
            ]
        ],
        'menuAttribute' => [
            'roles' => [$roles[2]]
        ],
        'menuItemCategory' => [
            'roles' => [$roles[2]]
        ],
        'menuAttributeSize' => [
            'roles' => [$roles[2]]
        ],
        'deals' => [
            'title' => 'Deals',
            'route' => route('deals.index'),
            'icon' => 'ti-basketball',
            'roles' => [$roles[0], $roles[2]],
            'child' => [
            ]
        ],
        'vouchers' => [
            'title' => 'Vouchers',
            'route' => route('vouchers.index'),
            'icon' => 'ti-gift',
            'roles' => [$roles[0], $roles[2]],
            'child' => [
            ]
        ],
        'dashboard_createVoucher' => [
            'title' => 'Send Voucher Promotions',
            'route' => route('dashboard_createVoucher'),
            'icon' => 'ti-gift',
            'roles' => [$roles[2]],
            'child' => [
            ]
        ],
        'dashboardPostVoucher' => [
            'roles' => [$roles[2]]
        ],
        'insertDeletePrinter' => [
            //'title' => 'insertDeletePrinter',
            'route' => route('insertDeletePrinter'),
            'icon' => 'ti-gift',
            'roles' => [$roles[2]],
            'child' => [
            ]
        ],
        'storeUsers' => [
            'roles' => [$roles[2]]
        ],
        'storeHolidays' => [
            'roles' => [$roles[2]]
        ],
        'storesSurgeCharges' => [
            'roles' => [$roles[2]]
        ],
        'dashboard' => [
            'roles' => $roles,
        ],
        'taxonomy'  => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'storesDeliveryLocationPrice' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'storesOnlineOrderTimings' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'storePickupLocations' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'logout' => [
            'roles' => $roles
        ],
        'post' => [
            'roles' => [$roles[0]]
        ],
        'product' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'getItemAttribute' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'menuItemAttributes' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
        'menuItemBanner' => [
            'roles' => [$roles[0], $roles[2], $roles[3]],
        ],
    ];
}
function storeCreateUpdateViewTabs($activeTab = 'All')
{
    if ($activeTab == 'StoreInfo') {
        return [
            'StoreInfo' => 'Store'
        ];
    }
    return [
        'StoreInfo' => 'Store',
        'StoreOnlineOrderTimingsPickup' => 'Pickup Order Timings',
        'StoreOnlineOrderTimingsDelivery' => 'Delivery Order Timings',
        'StoreTableBookingTimings' => 'Store Table Booking Timings',
        /*'StoreEmployee' => 'Store Employee',*/
        'StoreDeliveryLocationPrice' => 'Delivery Location Price',
        'StorePickupLocations' => 'Pickup Locations'
    ];
}
function getPostType($postType = null)
{
    $posts = postTypes();
    if(isset($posts[$postType]) && !empty($posts[$postType]))
    {
        return (isset($posts[$postType])?$posts[$postType]:'');
    }
    return;
}
function getTaxonomyType($postType = null, $taxonomy = null)
{
    $posts = postTypes();
    if(isset($posts[$postType]) && !empty($posts[$postType]))
    {
        if (isset($posts[$postType]['taxonomy'][$taxonomy]['title'])) {
            return ($posts[$postType]['taxonomy'][$taxonomy]['title']);
        }
    }
    return;
}
function getSupportLNG()
{
    return [
        'eng' => [
            'title' => 'English',
            'icon' => adminPublicPath().'/images/flags/ENGLISH.jpg',
            'type' => 'default'
        ],
        'fr' => [
            'title' => 'France',
            'icon' => adminPublicPath().'/images/flags/FRANCE.jpg',
            'type' => 'optional'
        ]
    ];
}
function dateFormat($date)
{
    return date('Y-m-d', strtotime($date));
}
function getLNGPost($posts, $lngCode)
{
    foreach ($posts as $post) {
        if ($lngCode == $post->post_lng) {
            return $post;
        }
    }
    return new \App\Posts;
}
function registerNavBarMenu()
{
    return [
        'primary_menu' => 'Primary Menu',
        'mobile_menu' => 'Mobile Menu',
        'footer_menu' => 'Footer Menu'
    ];
}
function getMenus($menufor = null){
    $menus = getChildMenus($menufor);
    $menuHtml = '<ul>';
    $menuHtml .= '<li><a href="'.url('/').'">Home</a></li>';
    foreach ($menus as $menu) {
        $menuHtml .= '<li><a href="'.url('/').'/'.($menu->link_target?$menu->link_target.'/':'').$menu->link_url.'">'.$menu->link_name.'</a>';
        if ($menu->childMenus) {
            $menuHtml .= '<ul>';
            foreach ($menu->childMenus as $childMenu) {
                $menuHtml .= '<li><a href="'.url('/').'/'.($childMenu->link_target?$childMenu->link_target.'/':'').$childMenu->link_url.'">'.$childMenu->link_name.'</a>';
            }
            $menuHtml .= '</ul>';
        }
        $menuHtml .= '</li>';
    }
    $menuHtml .= '</ul>';
    return $menuHtml;
}
function getChildMenus($menufor)
{
   $menuOptions = \App\Links::where('link_visible','Y')->where('links.link_rel',$menufor)->orderBy('link_order', 'ASC')->get();
   $menus = [];
   foreach($menuOptions as $menuOption)
   {
      if($menuOption->link_parent == 0)
      {
         if (in_array($menuOption->target_type, ['post','page'])) {
            $menuOption->target_type = '';
         }
         if (in_array($menuOption->link_target, ['post'])) {
            $menuOption->link_target = '';
         } else {
            $menuOption->link_target = 'category';
         }
         $menuOption->childMenus = getInnerChildMenu($menuOption->link_id, $menuOptions);
         $menus[] = $menuOption;
      }
   }
   return $menus;
}
function getInnerChildMenu($parent, $menuOptions)
{
   $menus = [];
   foreach($menuOptions as $menuOption)
   {
      if($menuOption->link_parent == $parent)
      {
         if (in_array($menuOption->target_type, ['post','page'])) {
            $menuOption->target_type = '';
         }
         if (in_array($menuOption->link_target, ['post'])) {
            $menuOption->link_target = '';
         } else {
            $menuOption->link_target = 'category';
         }
         $menuOption->childMenus = getInnerChildMenu($menuOption->link_id, $menuOptions);
         $menus[] = $menuOption;
      }
   }
   return $menus;
}
function getPostsByPostType($postType = null, $limit = 0)
{
    if ($limit == 0) {
        $limit = pagination();
    }
    $posts = \App\Posts::where('posts.post_type', $postType)
        ->leftJoin('posts as getImage','getImage.post_id','posts.guid')
        ->leftJoin('users as user','user.user_id','posts.user_id')
        ->select('posts.*','getImage.media as post_image', 'user.name as user_name','getImage.post_title as post_image_alt')
        ->where('posts.post_status', 'publish')
        ->where('posts.post_lng', defaultLanguage())
        ->paginate($limit);

    foreach($posts as &$post)
    {
        $post->extraFields = getPostMeta($post->post_id);
        $post->posted_date = date('M d, Y', strtotime($post->created_at));
        $post->posted_time = date('h:i A', strtotime($post->created_at));
    }
    return $posts;
}
function getPosts($postType = null)
{
    $order_by = Request()->input('order_by');
    $orderColumn = 'posts.menu_order';
    $orderBy = 'ASC';
    if ($order_by == 'new') {
        $orderColumn = 'posts.post_id';
        $orderBy = 'DESC';
    } else if($order_by == 'old') {
        $orderColumn = 'posts.post_id';
        $orderBy = 'ASC';
    }
    $term = Request()->input('term');
    $postTypes = getPostType($postType);
    $terms = [];
    if(!empty($postTypes['taxonomy']))
    {
        foreach ($postTypes['taxonomy'] as $key => $value) {
        $terms[$key] = \App\Terms::where('post_type', $postType)->where('term_group',$key)
            ->select(
            'name','slug','term_group','post_type','term_id',
            DB::raw("(SELECT meta_value FROM term_metas WHERE meta_key = 'service_icon' AND term_metas.term_id = terms.term_id) as term_image")
            )
            ->take(9)->get();
        }
    }

    $posts = \App\Posts::where('posts.post_type', $postType)
        ->leftJoin('posts as getImage','getImage.post_id','posts.guid')
        ->leftJoin('users as user','user.user_id','posts.user_id')
        ->select('posts.*','getImage.media as post_image', 'user.name as user_name','getImage.post_title as post_image_alt')
        ->where('posts.post_status', 'publish')
        ->where('posts.post_lng', defaultLanguage())
        ->where(function($query) use($term, $postType){
            if ($term) {
                if ($postType) {
                    $termIds = \App\Terms::where('slug', $term)->select('term_id');
                    $object_ids = \App\TermRelations::whereIn('term_id', $termIds)->select('object_id');
                    $query->whereIn('posts.post_id', $object_ids);
                }
            }
        })
        ->orderBy($orderColumn, $orderBy)
        ->paginate(pagination());
    foreach($posts as &$post)
    {
        $termRelations = \App\TermRelations::where('object_id', $post->post_id)->select('term_id');
        if(!empty($postTypes['taxonomy']))
        {
            $termsSelected = [];
            foreach ($postTypes['taxonomy'] as $key => $value) {
                $termsSelected[$key] = \App\Terms::whereIn('term_id', $termRelations)->where('term_group', $key)->get();
            }
            $post->category = $termsSelected;
        }
        $post->extraFields = getPostMeta($post->post_id);
        $post->posted_date = date('M d, Y', strtotime($post->created_at));
        $post->posted_time = date('h:i A', strtotime($post->created_at));
    }
    return compact('posts','terms');
}
function userStatus()
{
    return [
        '0' => 'Inactive',
        '1' => 'Active',
        '-1' => 'Suspended',
    ];
}
function itemDisplayIn()
{
    return [
        'Online Order',
        'Dine In',
        'Both'
    ];
}
function itemFor()
{
    return [
        'Breakfast', 'Lunch', 'Dinner'
    ];
}
function isDelicous()
{
    return ['Yes','No'];
}
function hourSlots()
{
    return [
        'collect_first_1_hour' => 'Callout + First 1 Hour',
        'collect_first_2_hour' => 'Callout + First 2 Hours',
        'additional_1_hour' => 'Additional 1 Hour',
        'additional_2_hour' => 'Additional 2 Hours',
        'additional_3_hour' => 'Additional 3 Hours',
        'additional_4_hour' => 'Additional 4 Hours',
        'additional_5_hour' => 'Additional 5 Hours',
        'additional_6_hour' => 'Additional 6 Hours',
    ];
}
function updatePostMeta($post_id = null, $meta_key = null, $meta_value = null){
    if (empty($post_id) && empty($meta_key)) {
        return;
    }
    if ($postMeta = \App\PostMetas::where('post_id', $post_id)->where('meta_key', $meta_key)->get()->first()) {
        $postMeta->meta_value = maybe_encode($meta_value);
        $postMeta->updated_at = new DateTime;
        $postMeta->save();
    } else {
        $postMeta = new \App\PostMetas;
        $postMeta->post_id = $post_id;
        $postMeta->meta_key = $meta_key;
        $postMeta->meta_value = maybe_encode($meta_value);
        $postMeta->created_at = new DateTime;
        $postMeta->updated_at = new DateTime;
        $postMeta->save();
    }
    return $post_id;
}

function getPostMeta($post_id = null, $meta_key = null){
    if (empty($post_id)) {
        return;
    }
    if ($meta_key) {
        return maybe_decode(\App\PostMetas::where('post_id', $post_id)->where('meta_key', $meta_key)->pluck('meta_value')->first());
    } else {
        $postMetas = \App\PostMetas::where('post_id', $post_id)->select('meta_key', 'meta_value')->get()->toArray();
        $postMetasData = [];
        foreach ($postMetas as &$postMeta) {
            $postMetasData[$postMeta['meta_key']] = maybe_decode($postMeta['meta_value']);
            unset($postMeta['meta_key']);
            unset($postMeta['meta_value']);
        }
        return $postMetasData;
    }
}

function updateTermMeta($term_id = null, $meta_key = null, $meta_value = null){
    if (empty($term_id) && empty($meta_key)) {
        return;
    }
    if ($termMeta = \App\TermMetas::where('term_id', $term_id)->where('meta_key', $meta_key)->get()->first()) {
        $termMeta->meta_value = maybe_encode($meta_value);
        $termMeta->updated_at = new DateTime;
        $termMeta->save();
    } else {
        $termMeta = new \App\TermMetas;
        $termMeta->term_id = $term_id;
        $termMeta->meta_key = $meta_key;
        $termMeta->meta_value = maybe_encode($meta_value);
        $termMeta->created_at = new DateTime;
        $termMeta->updated_at = new DateTime;
        $termMeta->save();
    }
    return $term_id;
}
function getTermMeta($term_id = null, $meta_key = null){
    if (empty($term_id)) {
        return;
    }
    if ($meta_key) {
        return maybe_decode(\App\TermMetas::where('term_id', $term_id)->where('meta_key', $meta_key)->pluck('meta_value')->first());
    } else {
        $termMetas = \App\TermMetas::where('term_id', $term_id)->select('meta_key', 'meta_value')->get()->toArray();
        foreach ($termMetas as &$termMeta) {
            $termMeta[$termMeta['meta_key']] = maybe_decode($termMeta['meta_value']);
            unset($termMeta['meta_key']);
            unset($termMeta['meta_value']);
        }
        return $termMetas;
    }
}
/*****term meta action******/
function addTermMetaBox($registerTerm,  $term_id)
{
    $termBoxHtml = '';
    switch ($registerTerm) {
        case 'service_category':
        case 'amc_category':
            $termBoxHtml = serviceTermMetaBox($term_id);
            break;

        default:
            $termBoxHtml = '';
            break;
    }
    echo $termBoxHtml;
}
function insertUpdateTermMetaBox($taxonomy, $request, $term_id)
{
    switch ($taxonomy) {
        case 'service_category':
        case 'amc_category':
            insertUpdateServiceTermMetaBox($request, $term_id);
            break;

        default:
            return;
            break;
    }
}

function serviceTermMetaBox($term_id)
{
    ob_start();
    $service_icon = getTermMeta($term_id, 'service_icon');
    $service_icon_img = ($service_icon?publicPath().'/'.$service_icon:'');
    ?>
    <div class="input-group row imageUploadGroup">
        <label class="col-form-label col-md-12" for="service_icon">Icon</label><br>
        <div class="col-md-12 row">
            <img src="<?php echo $service_icon_img; ?>" class="file-upload" id="service_icon-img" style="width: 100px; height: 100px; <?php echo (!$service_icon?'display:none;':'') ?>">
            <button type="button" data-eid="service_icon" style="<?php echo ($service_icon?'display:none;':'') ?>" class="btn btn-success setFeaturedImage">Select image</button>
            <button type="button" data-eid="service_icon" style="<?php echo (!$service_icon?'display:none;':'display:block;') ?>" class="btn btn-warning removeFeaturedImage">Remove image</button>
            <input type="hidden" name="service_icon" id="service_icon" value="<?php echo $service_icon; ?>">
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function insertUpdateServiceTermMetaBox($request, $term_id)
{
    updateTermMeta($term_id, 'service_icon', $request->input('service_icon'));
    updateTermMeta($term_id, 'link_post', $request->input('link_post'));
}
/*****end term meta action******/
/*****post meta action******/
function addPostMetaBox($post_type,  $post_id)
{
    $postBoxHtml = '';
    switch ($post_type) {
        case 'post':
            $postBoxHtml = postPostMetaBox($post_id);
            break;
        case 'service':
            $postBoxHtml = servicePostMetaBox($post_id);
            break;
        case 'amc':
            $postBoxHtml = servicePostMetaBox($post_id);
            break;
        case 'more_service':
            $postBoxHtml = moreServicePostMetaBox($post_id);
            break;
        case 'projects':
            $postBoxHtml = projectsPostMetaBox($post_id);
            break;
        case 'products':
            $postBoxHtml = productsPostMetaBox($post_id);
            $postBoxHtml .= projectsPostMetaBox($post_id);
            break;

        default:
            $postBoxHtml = '';
            break;
    }
    echo $postBoxHtml;
}
function insertUpdatePostMetaBox($post_type, $request, $post_id)
{
    switch ($post_type) {
        case 'post':
            insertUpdatePostPostMetaBox($request, $post_id);
            break;
        case 'service':
            insertUpdateServicePostMetaBox($request, $post_id);
            break;
        case 'amc':
            insertUpdateServicePostMetaBox($request, $post_id);
            break;
        case 'more_service':
            insertUpdateMoreServicePostMetaBox($request, $post_id);
            break;
        case 'projects':
            insertUpdateProjectsPostMetaBox($request, $post_id);
            break;
        case 'products':
            insertUpdateProductsPostMetaBox($request, $post_id);
            insertUpdateProjectsPostMetaBox($request, $post_id);
            break;

        default:
            return;
            break;
    }
}
/*****Post post meta action******/
function postPostMetaBox($post_id)
{
    ob_start();
    ?>

    <?php
    return ob_get_clean();
}
function insertUpdatePostPostMetaBox($request, $post_id)
{

}
/*****Service post meta action******/
function servicePostMetaBox($post_id)
{
    ob_start();
    ?>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="service_price">Price</label></h4>
        <div class="col-md-12">
            <input type="text" name="service_price" id="service_price" class="form-control form-control-lg InputNumber" placeholder="Price" value="<?php echo getPostMeta($post_id, 'service_price'); ?>">
        </div>
        <span class="md-line"></span>
    </div>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="service_attributes">Attributes</label></h4>
        <div class="input-group serviceAttributeItems card">
            <?php
            $service_attributes = getPostMeta($post_id, 'service_attributes');
            if (is_array($service_attributes) && !empty($service_attributes)) {
                $itemIndex = 0;
                foreach ($service_attributes as $service_attribute) {
                    echo '<div class="input-group serviceAttributeItem" data-itemID="'.$itemIndex.'">'
                            .'<div class="col-md-6">'
                                .'<label for="service_attributes_'.$itemIndex.'_name">Name</label>'
                                .'<input type="text" id="service_attributes_'.$itemIndex.'_name" name="service_attributes['.$itemIndex.'][name]" class="form-control form-control-lg" value="'.$service_attribute['name'].'" placeholder="Attribute Name">'
                            .'</div>'
                            .'<div class="col-md-5">'
                                .'<label for="service_attributes_'.$itemIndex.'_price">Price</label>'
                                .'<input type="text" id="service_attributes_'.$itemIndex.'_price" name="service_attributes['.$itemIndex.'][price]" class="form-control form-control-lg" value="'.$service_attribute['price'].'" placeholder="Attribute Price">'
                            .'</div>'
                            .'<div class="col-md-5"><label class="col-md-12"></label><button type="button" class="btn btn-danger removeAttrItem"><span class="pcoded-micon"><i class="ti-trash"></i></span></button></div>'
                        .'</div>';
                    $itemIndex++;
                }
            }
            ?>

        </div>
        <div class="input-group card">
            <button type="button" class="btn btn-success addAttrItem"><span class="pcoded-micon"><i class="ti-plus"></i></span></button>
        </div>
        <span class="md-line"></span>
        <style>
            .serviceAttributeItems {
                width: 100%;
                float: left;
                display: block;
                padding: 15px;
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function addAttributeItem()
                {
                    var itemLength = $('.serviceAttributeItem:last').attr('data-itemID');
                    if (itemLength == undefined) {
                        itemLength = 0;
                    } else {
                        itemLength++;
                    }
                    var itemHtml = '<div class="input-group serviceAttributeItem" data-itemID="'+itemLength+'">'
                        +'<div class="col-md-6">'
                            +'<label for="service_attributes_'+itemLength+'_name">Name</label>'
                            +'<input type="text" id="service_attributes_'+itemLength+'_name" name="service_attributes['+itemLength+'][name]" class="form-control form-control-lg" value="" placeholder="Attribute Name">'
                        +'</div>'
                        +'<div class="col-md-5">'
                            +'<label for="service_attributes_'+itemLength+'_price">Price</label>'
                            +'<input type="text" id="service_attributes_'+itemLength+'_price" name="service_attributes['+itemLength+'][price]" class="form-control form-control-lg" value="" placeholder="Attribute Price">'
                        +'</div>'
                        +'<div class="col-md-5"><label class="col-md-12"></label><button type="button" class="btn btn-danger removeAttrItem"><span class="pcoded-micon"><i class="ti-trash"></i></span></button></div>'
                    +'</div>';
                    $('.serviceAttributeItems').append(itemHtml);
                }
                <?php
                if (empty($service_attributes)) {
                    echo 'addAttributeItem();';
                }
                ?>

                $(document).on('click', '.removeAttrItem', function(event) {
                    event.preventDefault();
                    $(this).closest('.serviceAttributeItem').remove();
                });
                $(document).on('click', '.addAttrItem', function(event) {
                    event.preventDefault();
                    addAttributeItem();
                });
            });
        </script>
    </div>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="service_time_slots">Time Slots</label></h4>
        <?php
            $timeSlots = timeSlots();
            $service_time_slots = getPostMeta($post_id, 'service_time_slots');
            foreach ($timeSlots as $timeSlot) {
                echo '<label class="col-form-label col-md-6" for="'.$timeSlot.'">'.$timeSlot.'<input type="checkbox" class="form-control form-control-lg InputNumber" name="service_time_slots[]" style="float: right;width: 50%;" id="'.$timeSlot.'" value="'.$timeSlot.'"  '.(is_array($service_time_slots) && in_array($timeSlot, $service_time_slots)?'checked':'').'></label>';
            }
        ?>
    </div>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="service_disable_days">Disable Week Days</label></h4>
        <?php
        $service_disable_days = getPostMeta($post_id, 'service_disable_days');
        $disabledDays = ['MO','TU','WE','TH','FR','SA','SU'];
        foreach ($disabledDays as $disabledDay) {
            echo '<label class="col-form-label col-md-6" for="'.$disabledDay.'">'.$disabledDay.'<input type="checkbox" class="form-control form-control-lg InputNumber" name="service_disable_days[]" style="float: right;width: 80%;" id="'.$disabledDay.'" value="'.$disabledDay.'"  '.(is_array($service_disable_days) && in_array($disabledDay, $service_disable_days)?'checked':'').'></label>';
        }
        ?>
        <span class="md-line"></span>
    </div>
    <?php
    return ob_get_clean();
}
function insertUpdateServicePostMetaBox($request, $post_id)
{
    updatePostMeta($post_id, 'service_price', $request->input('service_price'));
    updatePostMeta($post_id, 'service_attributes', $request->input('service_attributes'));
    updatePostMeta($post_id, 'service_disable_days', $request->input('service_disable_days'));
    updatePostMeta($post_id, 'service_time_slots', $request->input('service_time_slots'));
}
/*****projects post meta action******/
function projectsPostMetaBox($post_id)
{
    ob_start();
    ?>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="project_gallery_description">Gallery Description</label></h4>
       <br>
       <textarea name="project_gallery_description" rows="5" id="project_gallery_description" class="form-control form-control-lg" placeholder="Gallery Description"><?php echo getPostMeta($post_id, 'project_gallery_description'); ?></textarea>
       <span class="md-line"></span>
    </div>
    <div class="input-group row ">
        <h4 class="col-md-12"><label class="col-form-label" for="">Gallery</label></h4>
        <?php
        $project_gallery = getPostMeta($post_id, 'project_gallery');
        $project_gallery_files = ['image1','image2','image3','image4','image5','image6','image7','image8','image9','image10','image11','image12','image13','image14','image15','image15'];
        foreach ($project_gallery_files as $project_gallery_file) {
            $image = (isset($project_gallery[$project_gallery_file]) && !empty($project_gallery[$project_gallery_file]) ? publicPath().'/'.$project_gallery[$project_gallery_file] : '');
            ?>
            <div class="col-md-6 imageUploadGroup input-group">
                <div class="col-md-12" style="border: 1px dashed #ccc">
                    <img src="<?php echo $image; ?>" class="file-upload" id="<?php echo $project_gallery_file ?>-img" style="width: 100px; height: 100px; <?php echo (!$image?'display:none;':'') ?>">
                    <button type="button" data-eid="<?php echo $project_gallery_file ?>" style="<?php echo ($image?'display:none;':'') ?>" class="btn btn-success setFeaturedImage">Select image</button>
                    <button type="button" data-eid="<?php echo $project_gallery_file ?>" style="<?php echo (!$image?'display:none;':'display:block;') ?>" class="btn btn-warning removeFeaturedImage">Remove image</button>
                    <input type="hidden" name="project_gallery[<?php echo $project_gallery_file ?>]" id="<?php echo $project_gallery_file ?>" value="<?php echo (isset($project_gallery[$project_gallery_file])?$project_gallery[$project_gallery_file]:''); ?>">
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
function insertUpdateProjectsPostMetaBox($request, $post_id)
{
    updatePostMeta($post_id, 'project_gallery', $request->input('project_gallery'));
    updatePostMeta($post_id, 'project_gallery_description', $request->input('project_gallery_description'));
}
/*****Service post meta action******/
function moreServicePostMetaBox($post_id)
{
    ob_start();
    $service_actions = getPostMeta($post_id, 'service_actions');
    $service_actions_labels = [
        'amc' => 'AMC',
        'one_time_job' => 'One Time Job',
        'projects' => 'Projects',
        'get_a_quote' => 'Get A Quote',
    ];
    ?>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="">Action Buttons</label></h4>
        <?php
        foreach ($service_actions_labels as $labelKey => $labelValue) {
            ?>
            <label  class="col-form-label col-md-3"for="<?php echo $labelKey ?>"><?php echo $labelValue ?> <input type="checkbox" name="service_actions[]" <?php echo (is_array($service_actions) && in_array($labelKey, $service_actions)?'checked':'') ?> id="<?php echo $labelKey ?>" value="<?php echo $labelKey ?>"></label>
            <?php
        }
        ?>
        <span class="md-line"></span>
    </div>
    <div class="input-group row">
        <h4 class="col-md-12"><label class="col-form-label" for="service_what_we_provide">What We Provide</label></h4>
       <br>
       <textarea name="service_what_we_provide" rows="5" id="service_what_we_provide" class="form-control form-control-lg" placeholder="What We Provide"><?php echo getPostMeta($post_id, 'service_what_we_provide'); ?></textarea>
       <span class="md-line"></span>
    </div>
    <div class="input-group row ">
        <h4 class="col-md-12"><label class="col-form-label" for="">Our Work</label></h4>
        <?php
        $service_our_work = getPostMeta($post_id, 'service_our_work');
        $service_our_work_files = ['work1','work2','work3','work4','work5','work6'];
        foreach ($service_our_work_files as $service_our_work_file) {
            $image = (isset($service_our_work[$service_our_work_file]) && !empty($service_our_work[$service_our_work_file]) ? publicPath().'/'.$service_our_work[$service_our_work_file] : '');
            ?>
            <div class="col-md-6 imageUploadGroup input-group">
                <div class="col-md-12" style="border: 1px dashed #ccc">
                    <img src="<?php echo $image; ?>" class="file-upload" id="<?php echo $service_our_work_file ?>-img" style="width: 100px; height: 100px; <?php echo (!$image?'display:none;':'') ?>">
                    <button type="button" data-eid="<?php echo $service_our_work_file ?>" style="<?php echo ($image?'display:none;':'') ?>" class="btn btn-success setFeaturedImage">Select image</button>
                    <button type="button" data-eid="<?php echo $service_our_work_file ?>" style="<?php echo (!$image?'display:none;':'display:block;') ?>" class="btn btn-warning removeFeaturedImage">Remove image</button>
                    <input type="hidden" name="service_our_work[<?php echo $service_our_work_file ?>]" id="<?php echo $service_our_work_file ?>" value="<?php echo (isset($service_our_work[$service_our_work_file])?$service_our_work[$service_our_work_file]:''); ?>">
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}
function insertUpdateMoreServicePostMetaBox($request, $post_id)
{
    updatePostMeta($post_id, 'service_actions', $request->input('service_actions'));
    updatePostMeta($post_id, 'service_what_we_provide', $request->input('service_what_we_provide'));
    updatePostMeta($post_id, 'service_our_work', $request->input('service_our_work'));
}
/*****products post meta action******/
function productsPostMetaBox($post_id)
{
    ob_start();
    $postTitle = getPostType('products');
    $attributeVariations = [];
    if (!empty($postTitle['taxonomy'])) {
        foreach ($postTitle['taxonomy'] as $taxonomyKey => $taxonomyValue) {
            if ($taxonomyValue['hasVariations'] == true) {
                $terms = \App\Terms::where('term_group', $taxonomyKey)->get();
                foreach ($terms as $term) {
                    $slug = $term->slug;
                    $attributeVariations[] = $slug;
                }
            }
        }
    }
    $productMeta = getPostMeta($post_id);
    $product_type = (isset($productMeta['product_type'])?$productMeta['product_type']:'');
    $_regular_price = (isset($productMeta['_regular_price'])?$productMeta['_regular_price']:'');
    $_sale_price = (isset($productMeta['_sale_price'])?$productMeta['_sale_price']:'');
    $_sale_price_dates_from = (isset($productMeta['_sale_price_dates_from'])?$productMeta['_sale_price_dates_from']:'');
    $_sale_price_dates_to = (isset($productMeta['_sale_price_dates_to'])?$productMeta['_sale_price_dates_to']:'');
    $_tax_status = (isset($productMeta['_tax_status'])?$productMeta['_tax_status']:'');
    $_tax_class = (isset($productMeta['_tax_class'])?$productMeta['_tax_class']:'');
    $_sku = (isset($productMeta['_sku'])?$productMeta['_sku']:'');
    $_manage_stock = (isset($productMeta['_manage_stock'])?$productMeta['_manage_stock']:'');
    $_stock = (isset($productMeta['_stock'])?$productMeta['_stock']:'');
    $_stock_status = (isset($productMeta['_stock_status'])?$productMeta['_stock_status']:'');
    $attribute_variations = (isset($productMeta['attribute_variations'])?$productMeta['attribute_variations']:'');
    ?>
    <link rel="stylesheet" type="text/css" href="<?php echo adminPublicPath() ?>/css/admin.css">
    <style type="text/css">
        .product_data_tabs a:before, .handlediv:before{
            content: '';
            display: none;
        }
        .variationGroups {
            padding: 10px;
        }
        .wc-metaboxes-wrapper .woocommerce_variable_attributes {
            box-shadow: 0px 0px 7px 1px #ccc;
            padding: 0px;
            margin-bottom: 15px !important;
        }
    </style>
    <div id="woocommerce-product-data" class="postbox " style="zoom: 1;">
       <h2 class="hndle ui-sortable-handle">
          <span>
             Product data
             <span class="type_box hidden">
                —
                <label for="product-type">
                   <select id="product-type" name="product_type" class="form-control">
                      <optgroup label="Product Type">
                         <option value="simple" <?php echo ($product_type == 'simple'?'selected':'') ?>>Simple product</option>
                         <option value="variable" <?php echo ($product_type == 'variable'?'selected':'') ?>>Variable product</option>
                      </optgroup>
                   </select>
                </label>
             </span>
          </span>
       </h2>
       <div class="inside">
          <div class="panel-wrap product_data">
             <ul class="product_data_tabs wc-tabs">
                <li class="general_options general_tab active">
                   <a href="#general_product_data"><span><i class="ti-settings"></i> General</span></a>
                </li>
                <li class="inventory_options inventory_tab show_if_simple_variable">
                   <a href="#inventory_product_data"><span><i class="ti-bag"></i> Inventory</span></a>
                </li>
                <li class="variations_options variations_tab variations_tab show_if_variable" style="display: none;">
                   <a href="#variable_product_options"><span><i class="ti-layout"></i> Variations</span></a>
                </li>
             </ul>
             <div id="general_product_data" class="panel woocommerce_options_panel" style="display: block;">
                <div class="options_group pricing show_if_simple" style="display: none;">
                   <p class="form-field _regular_price_field ">
                      <label for="_regular_price">Regular price (AUD $)</label><input type="text" class="short wc_input_price form-control InputNumber" style="" name="_regular_price" id="_regular_price" value="<?php echo $_regular_price ?>" placeholder="">
                   </p>
                   <p class="form-field _sale_price_field ">
                      <label for="_sale_price">Sale price (AUD $)</label><input type="text" class="short wc_input_price form-control InputNumber" style="" name="_sale_price" id="_sale_price" value="<?php echo $_sale_price ?>" placeholder=""> </span>
                   </p>
                   <p class="form-field sale_price_dates_fields">
                      <label for="_sale_price_dates_from">Sale price dates</label>
                      <input type="text" class="short form-control sale_price_dates_from" name="_sale_price_dates_from" id="_sale_price_dates_from" value="<?php echo $_sale_price_dates_from ?>" placeholder="From… YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                      <input type="text" class="short form-control sale_price_dates_to" name="_sale_price_dates_to" id="_sale_price_dates_to" value="<?php echo $_sale_price_dates_to ?>" placeholder="To…  YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                   </p>
                </div>
                <div class="options_group show_if_simple_variable">
                   <p class=" form-field _tax_status_field">
                      <label for="_tax_status">Tax status</label>

                      <select style="" id="_tax_status" name="_tax_status" class="select short form-control">
                         <option value="taxable" <?php echo ($_tax_status == 'taxable'?'selected':'') ?>>Taxable</option>
                         <option value="shipping" <?php echo ($_tax_status == 'shipping'?'selected':'') ?>>Shipping only</option>
                         <option value="none" <?php echo ($_tax_status == 'none'?'selected':'') ?>>None</option>
                      </select>
                   </p>
                   <p class=" form-field _tax_class_field">
                      <label for="_tax_class">Tax class</label>

                      <select style="" id="_tax_class" name="_tax_class" class="select short form-control">
                         <option value="Standard" <?php echo ($_tax_class == 'Standard'?'selected':'') ?>>Standard</option>
                         <option value="reduced-rate" <?php echo ($_tax_class == 'reduced-rate'?'selected':'') ?>>Reduced rate</option>
                         <option value="zero-rate" <?php echo ($_tax_class == 'zero-rate'?'selected':'') ?>>Zero rate</option>
                      </select>
                   </p>
                </div>
             </div>
             <div id="inventory_product_data" class="panel woocommerce_options_panel hidden" style="display: none;">
                <div class="options_group">
                   <p class="form-field _sku_field ">
                      <label for="_sku"><abbr title="Stock Keeping Unit">SKU</abbr></label><
                      <input type="text" class="short form-control" style="" name="_sku" id="_sku" value="<?php echo $_sku ?>" placeholder="">
                   </p>
                   <p class="form-field _manage_stock_field show_if_simple show_if_variable" style="display: none;">
                      <label for="_manage_stock">Manage stock?</label>
                      <input type="checkbox" class="checkbox form-control" style="" name="_manage_stock" id="_manage_stock" <?php echo ($_manage_stock == 'yes'?'checked':'') ?> value="yes"> <span class="description">Enable stock management at product level</span>
                   </p>
                   <div class="stock_fields show_if_simple show_if_variable" style="display: none;">
                      <p class="form-field _stock_field ">
                         <label for="_stock">Stock quantity</label>
                         <input type="number" class="short wc_input_stock form-control" style="" name="_stock" id="_stock" value="<?php echo $_stock ?>" placeholder="" step="any">
                      </p>
                   </div>
                   <p class="stock_status_field hide_if_variable form-field _stock_status_field" style="display: none;">
                      <label for="_stock_status">Stock status</label>
                      <select style="" id="_stock_status" name="_stock_status" class="select short form-control">
                         <option value="instock" <?php echo ($_stock_status == 'instock'?'selected':'') ?>>In stock</option>
                         <option value="outofstock" <?php echo ($_stock_status == 'outofstock'?'selected':'') ?>>Out of stock</option>
                      </select>
                   </p>
                </div>
             </div>
             <div id="variable_product_options" class="panel wc-metaboxes-wrapper hidden" style="display: none;">
                <div id="variable_product_options_inner">
                   <div class="toolbar toolbar-top">
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-control" id="selectAttribte">
                                    <option value="">Select</option>
                                    <?php
                                    if ($attributeVariations) {
                                        foreach ($attributeVariations as $variationValue) {
                                            ?>
                                               <option value="<?php echo $variationValue ?>"><?php echo $variationValue ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <a class="do_variation_action btn btn-success">Add variation</a>
                            </div>
                        </div>
                        <div class="variationGroups">
                            <?php
                            if (!empty($attribute_variations) && is_array($attribute_variations)) {
                                $index = 0;
                                foreach ($attribute_variations as $attribute_variation) {
                                    $term_slug = (isset($attribute_variation['term_slug'])?$attribute_variation['term_slug']:'');
                                   echo getVariationGroupItem($index, $term_slug, $attribute_variation);
                                   $index++;
                                }
                            }
                            ?>
                        </div>
                   </div>
                </div>
             </div>
          </div>
       </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(document).on('change', '#product-type', function(event) {
                event.preventDefault();
                var value = $(this).val();
                if (value == 'simple') {
                    $('.show_if_variable').fadeOut();
                    $('.show_if_simple').fadeIn();
                } else if (value == 'variable') {
                    $('.show_if_variable').fadeIn();
                    $('.show_if_simple').fadeOut();
                }
            });
            $('#product-type').trigger('change');

            $(document).on('click', '.do_variation_action', function(){
                var value = $('#selectAttribte').val();
                if (value == '') {
                    window.alert('Please select attribute');
                    return false;
                }
                var index = $('.woocommerce_variable_attributes:last-child').attr('data-index');
                if(index == undefined){
                    index = 0;
                } else {
                    index++;
                }
                $.ajax({
                    url: '<?php echo route('product.attribute') ?>',
                    type: 'GET',
                    data: {term: value, index: index},
                })
                .done(function(response) {
                    $('.variationGroups').append(response);
                });
            });
            $(document).on('click', '.toggle_variation', function(event) {
                event.preventDefault();
                var toggleID = $(this).attr('data-toggleID');
                $('#'+toggleID).toggle();
            });
            $(document).on('click', '.remove_variation', function(event) {
                event.preventDefault();
                $(this).closest('.woocommerce_variable_attributes').remove();
            });
            $(document).on('click', '.product_data_tabs li a', function(event) {
                event.preventDefault();
                $(this).parent('li').addClass('active');
                $(this).closest('li').siblings('li').removeClass('active');
                var href = $(this).attr('href');
                $('.panel').fadeOut();
                $(href).fadeIn();
            });
            $(document).on('change', '.attribute_variations_termChange', function(event) {
                event.preventDefault();
                var index = $(this).closest('.woocommerce_variable_attributes').attr('data-index');
                if(index == undefined){
                    index = 0;
                }
                var indextext = $(this).closest('.woocommerce_variable_attributes').find('.attribute_variations_termChange option:selected').text();
                $('#attribute_variations_'+index+'_term_name').val(indextext);
            });
            $('.sale_price_dates_from').datepicker({
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
                minDate: 0,
                onSelect: function(selectedDate) {
                    var $this = $(this);
                    var min = new Date(selectedDate);
                    $this.closest('.sale_price_dates_fields').find(".sale_price_dates_to").datepicker('option', 'minDate', min);
                }
            });

            $('.sale_price_dates_to').datepicker({
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                changeMonth: true,
                changeYear: true,
                minDate: 0,
                onSelect: function(selectedDate) {
                    var $this = $(this);
                    var max = new Date(selectedDate);
                    $this.closest('.sale_price_dates_fields').find(".sale_price_dates_from").datepicker('option', 'maxDate', mxa);
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
function getVariationGroupItem($index = 0, $termSlug = '', $attribute_variation = [])
{
    ob_start();
    $terms = \App\Terms::where('term_group', $termSlug)->get();
    ?>
    <div class="woocommerce_variable_attributes wc-metabox variation-needs-update open" data-index="<?php echo $index; ?>">
        <input type="hidden" name="attribute_variations[<?php echo $index ?>][term_slug]" value="<?php echo $termSlug ?>">
        <div class="data">
            <div class="row">
                <div class="col-md-7">

                    <select name="attribute_variations[<?php echo $index ?>][term]" class="form-control attribute_variations_termChange">
                        <?php
                        $selectedTermName = '';
                        $termSelected = (isset($attribute_variation['term'])?$attribute_variation['term']:'');
                        foreach ($terms as $term) {
                            if ($term->term_id == $termSelected) {
                                $selectedTermName = $term->name;
                            }
                            ?>
                            <option value="<?php echo $term->term_id ?>" <?php echo ($term->term_id == $termSelected?'selected':'') ?>><?php echo $term->name ?></option>
                            <?php
                        }
                        ?>
                    </select>
                    <input type="hidden" id="attribute_variations_<?php echo $index ?>_term_name" name="attribute_variations[<?php echo $index ?>][term_name]" value="<?php echo $selectedTermName; ?>">
                </div>
                <div class="col-md-5">
                    <a href="#" class="toggle_variation btn btn-success" data-toggleID="variationBody<?php echo $index ?>"><i class="ti-angle-down"></i></a> |
                    <a href="#" class="remove_variation delete btn btn-danger">Remove</a>
                </div>
            </div>
        </div>
       <div class=" wc-metabox-content" id="variationBody<?php echo $index ?>" style="display: none;">
          <div class="data">
             <p class="form-field variable_sku2_field form-row">
                <label for="variable_sku2"><abbr title="Stock Keeping Unit">SKU</abbr></label>
                <input type="text" class="short form-control" style="" name="attribute_variations[<?php echo $index ?>][variable_sku]" id="variable_sku2" value="<?php echo (isset($attribute_variation['variable_sku'])?$attribute_variation['variable_sku']:'') ?>" placeholder="">
             </p>
             <div class="variable_pricing">
                <p class="form-field variable_regular_price_2_field form-row form-row-first">
                   <label for="variable_regular_price_2">Regular price (AUD $)</label>
                   <input type="text" class="short wc_input_price form-control InputNumber" style="" name="attribute_variations[<?php echo $index ?>][variable_regular_price]" id="variable_regular_price_2" value="<?php echo (isset($attribute_variation['variable_regular_price'])?$attribute_variation['variable_regular_price']:'') ?>" placeholder="Variation price (required)">
                </p>
                <p class="form-field variable_sale_price2_field form-row form-row-last">
                   <label for="variable_sale_price2">Sale price (AUD $)</label>
                   <input type="text" class="short wc_input_price form-control InputNumber" style="" name="attribute_variations[<?php echo $index ?>][variable_sale_price]" id="variable_sale_price2" value="<?php echo (isset($attribute_variation['variable_sale_price'])?$attribute_variation['variable_sale_price']:'') ?>" placeholder="">
                </p>
                <div class="form-field sale_price_dates_fields">
                   <p class="form-row form-row-first">
                      <label>Sale start date</label>
                      <input type="text" class=" form-control" name="attribute_variations[<?php echo $index ?>][variable_sale_price_dates_from]" value="<?php echo (isset($attribute_variation['variable_sale_price_dates_from'])?$attribute_variation['variable_sale_price_dates_from']:'') ?>" placeholder="From… YYYY-MM-DD" id="variable_sale_price_dates_from_<?php echo $index ?>">
                   </p>
                   <p class="form-row form-row-last">
                      <label>Sale end date</label>
                      <input type="text" class=" form-control" name="attribute_variations[<?php echo $index ?>][variable_sale_price_dates_to]" value="<?php echo (isset($attribute_variation['variable_sale_price_dates_to'])?$attribute_variation['variable_sale_price_dates_to']:'') ?>" placeholder="To…  YYYY-MM-DD" id="variable_sale_price_dates_to_<?php echo $index ?>">
                   </p>
                   <script type="text/javascript">
                       $('#variable_sale_price_dates_from_<?php echo $index ?>').datepicker({
                           dateFormat: 'yy-mm-dd',
                           showButtonPanel: true,
                           changeMonth: true,
                           changeYear: true,
                           minDate: 0,
                           onSelect: function(selectedDate) {
                               var min = new Date(selectedDate);
                               $("#variable_sale_price_dates_to_<?php echo $index ?>").datepicker('option', 'minDate', min);
                           }
                       });

                       $('#variable_sale_price_dates_to_<?php echo $index ?>').datepicker({
                           dateFormat: 'yy-mm-dd',
                           showButtonPanel: true,
                           changeMonth: true,
                           changeYear: true,
                           minDate: 0,
                           onSelect: function(selectedDate) {
                               var $this = $(this);
                               var max = new Date(selectedDate);
                               $("#variable_sale_price_dates_from_<?php echo $index ?>").datepicker('option', 'maxDate', mxa);
                           }
                       });
                   </script>
                </div>
             </div>
             <div>
                <p class="form-row form-row-full variable_stock_status form-field variable_stock_status2_field">
                   <label for="variable_stock_status2">Stock status</label>
                   <?php $variable_stock_status = (isset($attribute_variation['variable_stock_status'])?$attribute_variation['variable_stock_status']:'') ?>
                   <select style="" id="variable_stock_status2" name="attribute_variations[<?php echo $index ?>][variable_stock_status]" class="select short form-control">
                      <option value="instock" <?php echo ($variable_stock_status == 'instock'?'selected':'') ?>>In stock</option>
                      <option value="outofstock" <?php echo ($variable_stock_status == 'outofstock'?'selected':'') ?>>Out of stock</option>
                      <option value="onbackorder" <?php echo ($variable_stock_status == 'onbackorder'?'selected':'') ?>>On backorder</option>
                   </select>
                </p>
                <p class="form-field variable_weight2_field form-row form-row-first hide_if_variation_virtual">
                   <label for="variable_weight2">Weight (kg)</label>
                   <input type="text" class="short wc_input_decimal form-control" style="" name="attribute_variations[<?php echo $index ?>][variable_weight]" id="variable_weight2" value="<?php echo (isset($attribute_variation['variable_weight'])?$attribute_variation['variable_weight']:'') ?>" placeholder="5">
                </p>
                <p class="form-field variable_stock2_field form-row form-row-last">
                   <label for="variable_stock2">Stock quantity</label>
                   <input type="number" class="short wc_input_stock form-control" style="" name="attribute_variations[<?php echo $index ?>][variable_stock]" id="variable_stock2" value="<?php echo (isset($attribute_variation['variable_stock'])?$attribute_variation['variable_stock']:0) ?>" placeholder="" step="any">
                </p>
             </div>
             <div>
                <p class="form-field variable_description2_field form-row form-row-full">
                   <label for="variable_description2">Description</label>
                   <textarea class="short form-control" style="" name="attribute_variations[<?php echo $index ?>][variable_description]" id="variable_description2" placeholder="" rows="2" cols="20"><?php echo (isset($attribute_variation['variable_description'])?$attribute_variation['variable_description']:'') ?></textarea>
                </p>
             </div>
          </div>
       </div>
    </div>
    <?php
    return ob_get_clean();
}
function insertUpdateProductsPostMetaBox($request, $post_id)
{
    updatePostMeta($post_id, 'product_type', $request->input('product_type'));
    updatePostMeta($post_id, '_regular_price', $request->input('_regular_price'));
    updatePostMeta($post_id, '_sale_price', $request->input('_sale_price'));
    updatePostMeta($post_id, '_sale_price_dates_from', $request->input('_sale_price_dates_from'));
    updatePostMeta($post_id, '_sale_price_dates_to', $request->input('_sale_price_dates_to'));
    updatePostMeta($post_id, '_tax_status', $request->input('_tax_status'));
    updatePostMeta($post_id, '_tax_class', $request->input('_tax_class'));
    updatePostMeta($post_id, '_sku', $request->input('_sku'));
    updatePostMeta($post_id, '_manage_stock', $request->input('_manage_stock'));
    updatePostMeta($post_id, '_stock', $request->input('_stock'));
    updatePostMeta($post_id, '_stock_status', $request->input('_stock_status'));
    updatePostMeta($post_id, 'attribute_variations', $request->input('attribute_variations'));
}
/*****end post meta action******/



/***** Comment Meta********/
function getCommentMeta($comment_id = null, $meta_key = null){
    if (empty($comment_id)) {
        return;
    }
    if ($meta_key) {
        return maybe_decode(\App\CommentMeta::where('comment_id', $comment_id)->where('meta_key', $meta_key)->pluck('meta_value')->first());
    } else {
        $commentMetas = \App\CommentMeta::where('comment_id', $comment_id)->select('meta_key', 'meta_value')->get()->toArray();
        $commentMetasData = [];
        foreach ($commentMetas as &$commentMeta) {
            $commentMetasData[$commentMeta['meta_key']] = maybe_decode($commentMeta['meta_value']);
            unset($commentMeta['meta_key']);
            unset($commentMeta['meta_value']);
        }
        return $commentMetasData;
    }
}

function updateCommentMeta($comment_id = null, $meta_key = null, $meta_value = null){
    if (empty($comment_id) && empty($meta_key)) {
        return;
    }
    if ($commentMeta = \App\CommentMeta::where('comment_id', $comment_id)->where('meta_key', $meta_key)->get()->first()) {
        $commentMeta->meta_value = maybe_encode($meta_value);
        $commentMeta->updated_at = new DateTime;
        $commentMeta->save();
    } else {
        $commentMeta = new \App\CommentMeta;
        $commentMeta->comment_id = $comment_id;
        $commentMeta->meta_key = $meta_key;
        $commentMeta->meta_value = maybe_encode($meta_value);
        $commentMeta->created_at = new DateTime;
        $commentMeta->updated_at = new DateTime;
        $commentMeta->save();
    }
    return $comment_id;
}
/***** User Meta********/
function getUserMeta($user_id = null, $meta_key = null){
    if (empty($user_id)) {
        return;
    }
    if ($meta_key) {
        return maybe_decode(\App\UserMetas::where('user_id', $user_id)->where('meta_key', $meta_key)->pluck('meta_value')->first());
    } else {
        $userMetas = \App\UserMetas::where('user_id', $user_id)->select('meta_key', 'meta_value')->get()->toArray();
        $userMetasData = [];
        foreach ($userMetas as &$userMeta) {
            $userMetasData[$userMeta['meta_key']] = maybe_decode($userMeta['meta_value']);
            unset($userMeta['meta_key']);
            unset($userMeta['meta_value']);
        }
        return $userMetasData;
    }
}

function updateUserMeta($user_id = null, $meta_key = null, $meta_value = null){
    if (empty($user_id) && empty($meta_key)) {
        return;
    }
    if ($userMeta = \App\UserMetas::where('user_id', $user_id)->where('meta_key', $meta_key)->get()->first()) {
        $userMeta->meta_value = maybe_encode($meta_value);
        $userMeta->updated_at = new DateTime;
        $userMeta->save();
    } else {
        $userMeta = new \App\UserMetas;
        $userMeta->user_id = $user_id;
        $userMeta->meta_key = $meta_key;
        $userMeta->meta_value = maybe_encode($meta_value);
        $userMeta->created_at = new DateTime;
        $userMeta->updated_at = new DateTime;
        $userMeta->save();
    }
    return $user_id;
}

function createUpdateSiteMapXML($postUrl){
    return
    $hasUrl = false;
    $sitemapPath = base_path('sitemap.xml');
    $sitemapPath = str_replace('backend/', '', $sitemapPath);
    $xmlObjects = simplexml_load_file($sitemapPath);

    $xmlRow = '';
    $existRow = false;
    if (!empty($xmlObjects->url)) {
        foreach($xmlObjects->url as $xmlObject){
            if ($xmlObject->loc == $postUrl) {
                $existRow = true;
                $xmlRow .= '<url>
                        <loc>'.$xmlObject->loc.'</loc>
                      <lastmod>'.date('c',time()).'</lastmod>
                      <priority>'.$xmlObject->priority.'</priority>
                   </url>';
            } else {
                $xmlRow .= '<url>
                      <loc>'.$xmlObject->loc.'</loc>
                      <lastmod>'.$xmlObject->lastmod.'</lastmod>
                      <priority>'.$xmlObject->priority.'</priority>
                   </url>';
            }
        }
    }
    if ($existRow == false) {
        $xmlRow .= '<url>
                      <loc>'.$postUrl.'</loc>
                      <lastmod>'.date('c',time()).'</lastmod>
                      <priority>0.5</priority>
                   </url>';
    }

    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        <!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->
           '.$xmlRow.'
        </urlset>';

    $dom = new \DOMDocument;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadXML($xmlContent);
    $dom->save($sitemapPath);
}
function deleteSiteMapXML($postUrl){
    return;
    $hasUrl = false;
    $sitemapPath = base_path('sitemap.xml');
    $sitemapPath = str_replace('backend/', '', $sitemapPath);
    $xmlObjects = simplexml_load_file($sitemapPath);

    $xmlRow = '';
    if (!empty($xmlObjects->url)) {
        foreach($xmlObjects->url as $xmlObject){
            if ($xmlObject->loc != $postUrl) {
                $xmlRow .= '<url>
                        <loc>'.$xmlObject->loc.'</loc>
                      <lastmod>'.$xmlObject->lastmod.'</lastmod>
                      <priority>'.$xmlObject->priority.'</priority>
                   </url>';
            }
        }
    }

    $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset
              xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
                    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        <!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->
           '.$xmlRow.'
        </urlset>';

    $dom = new \DOMDocument;
    $dom->preserveWhiteSpace = FALSE;
    $dom->loadXML($xmlContent);
    $dom->save($sitemapPath);
}

function headerCommon($post)
{
    return '<aside id="colorlib-hero">
        <div class="flexslider">
            <ul class="slides">
            <li style="background-image: url( '.publicPath().'/'.$post->post_image.');" data-stellar-background-ratio="0.5">
                <div class="overlay"></div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 col-xs-12 col-md-offset-3 slider-text">
                            <div class="slider-text-inner text-center">
                                <div class="desc">
                                    <span class="icon"><i class="flaticon-cutlery"></i></span>
                                    <h1>'.$post->post_title.'</h1>
                                    <p><span><a href="'.url('/').'">Home</a></span> - <span>'.$post->post_title.'</span></p>
                                    <div class="desc2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            </ul>
        </div>
    </aside>';
}
function getDateRange($days = 2)
{
    $startDate = date('Ymd');
    $daysRange = [];
    $daysRange[] = $startDate;
    $endDate = date('Ymd', strtotime("+".$days." days"));
    $index = 1;
    while ($startDate <= $endDate) {
        $startDate = date('Ymd', strtotime("+".$index." days"));
        $daysRange[] = $startDate;
        $index++;
    }
    return $daysRange;
}
function addToCartButton($menuItem, $pageType = 'itemPage')
{
    if (empty($menuItem)) {
        $menuItem = new \App\MenuItems();
    }
    $price = itemShowPrice($menuItem);
    $attributes = ' data-store_id="'.$menuItem->store_id.'"';
    $attributes .= ' data-menu_item_id="'.$menuItem->menu_item_id.'"';
    $attributes .= ' data-item_name="'.$menuItem->item_name.'"';
    $attributes .= ' data-item_price="'.$price.'"';
    $attributes .= ' data-item_page="'.$pageType.'"';
    return '<div class="dish-btn-qblk">
            <a href="javascript:void(0)" class="dish-btn addToCartItem addTOCART_'.$menuItem->menu_item_id.'" '.$attributes.'>Add +</a>
            <div class="dish-btn-qty dish_qty_'.$menuItem->menu_item_id.'" style="display:none;">
               <div class="input-group">
                  <span class="input-group-btn">
                  <button type="button" class="quantity-left-minus btn btn-number" data-item_page="cartPage" '.$attributes.' data-type="minus" data-field="">
                  <span>-</span>
                  </button>
                  </span>
                  <input type="text" name="quantity" class="form-control input-number quantity_'.$menuItem->menu_item_id.'" value="1" min="1" max="100">
                  <span class="input-group-btn">
                  <button type="button" class="quantity-right-plus btn btn-number" data-item_page="cartPage" '.$attributes.' data-type="plus" data-field="">
                  <span>+</span>
                  </button>
                  </span>
               </div>
            </div></div>';
}

function getCartHtml($showToogle = false)
{
    ob_start();/*
    Session::put ( 'cartData' );
    Session::save();*/
    
      $cartDatas = Session::get ( 'cartData' );
      if (empty($cartDatas)) {
        $cartDatas = [];
      }
      if (!empty($cartDatas)) {
      $delivery_pickup_address = Session::get('delivery_pickup_address');
      $minimumOrderPrice = 0;
      $store_id = array_column($cartDatas, 'store_id');
      $store_id = reset($store_id);
      $store = \App\Stores::where('store_id', $store_id)->get()->first();
      if ($store->store_extra_charges == 'yes') {
          if ($delivery_pickup_address['order_type'] == 'Delivery') {
            $suburb = $delivery_pickup_address['suburb'];
            $pincode = $delivery_pickup_address['pincode'];
            $store_location = \App\StoreDeliveryLocationPrice::where(function($query) use($pincode, $suburb){
                                  /*if ($suburb) {
                                      $query->orwhere('suburb', 'LIKE', '%'.$suburb.'%');
                                      $query->orwhere('city', 'LIKE', '%'.$suburb.'%');
                                  }*/
                                  if ($pincode) {
                                      $query->where('postal_code', 'LIKE', '%'.$pincode.'%');
                                  }
                              })->get()->first();

            if ($store_location) {
                $minimumOrderPrice = $store_location->minimum_delivery_order;
                $pickDeliveryPrice = $store_location->minimum_delivery_charge;
            } else {
                $minimumOrderPrice = 0;
                $pickDeliveryPrice = 0;
            }
          } else {
            $minimumOrderPrice = 0;
            $pickDeliveryPrice = 0;
          }
      } else {
        $minimumOrderPrice = 0;
        $pickDeliveryPrice = 0;
      }
       /**/
       /* echo '<pre>';
        print_r($cartDatas);
        echo '<pre>';*/
     ?>
      <div class="cart-details">
         <div class="container">
            <div class="title">
               <h2>Cart</h2>
               <span class="close-ico"><a onclick="$('.cart-details').toggle();"><img src="<?php echo asset('public') ?>/images/close.png"></a></span>
            </div>
            <div class="cart-data">
            <?php
              $subTotal = 0;
              $totalItems = 0;
              foreach ($cartDatas as $cartData) {
                $menuItem = \App\MenuItems::where('menu_item_id', $cartData['menu_item_id'])->where('store_id', $cartData['store_id'])->get()->first();
                $subTotal += $cartData['item_total_price'];
                $attributes = ' data-store_id="'.$menuItem->store_id.'"';
                $attributes .= ' data-menu_item_id="'.$menuItem->menu_item_id.'"';
                $attributes .= ' data-item_name="'.$menuItem->item_name.'"';
                $attributes .= ' data-item_price="'.$cartData['item_price'].'"';
                $attributes .= ' data-item_page="cartPage"';
                $attributeIDS = '';
                $totalItems += $cartData['item_quantity'];
                if (isset($cartData['attributes'])) {
                    $attributes .= ' data-type="attribute"';
                    $arrayKeys = array_keys($cartData['attributes']);
                    $attributeIDS = implode('-',$arrayKeys);
                    $attributes .= ' data-item_attributeIDS="'.$attributeIDS.'"';
                }
                if ($menuItem->item_is == 'Attributes') {
                    $attributes .= ' data-type="attribute"';
                }
                $totalItemPrice = $cartData['item_total_price'];
                ?>
                <div class="order-menu">
                   <div class="col-xs-4 pl-0">
                        <h2><?php echo $menuItem->item_name ?><br>
                            <?php
                            $totalAttrButePrice = 0;
                            $totalattr_price = 0;
                            $attributeName = [];
                            if (isset($cartData['attributes'])) {
                                foreach ($cartData['attributes'] as $attribute) {
                                    $attributeName[] = $attribute['attr_name'];
                                    $price_type = '+';
                                    if ($attribute['attr_type'] == 'remove') {
                                        $price_type = '-';
                                        $subTotal -= $attribute['attr_total_price'];
                                        $totalattr_price -= $attribute['attr_price'];
                                        $totalAttrButePrice -= $attribute['attr_total_price'];
                                    } else{
                                        $subTotal += $attribute['attr_total_price'];
                                        $totalattr_price += $attribute['attr_price'];
                                        $totalAttrButePrice += $attribute['attr_total_price'];
                                    }
                                }
                                ?>
                                <span class="menu-subitems"><?php echo implode(', ', $attributeName); ?></span>
                                <?php
                            }
                            ?>
                        </h2>
                   </div>
                   <div class="col-xs-5"><span class="amt"><?php echo priceFormat($cartData['item_price']+$totalattr_price) ?></span></div>
                   <div class="col-xs-3">
                      <span class="dish-btn dish-btn-qblk">
                         <div class="dish-btn-qty">
                            <div class="input-group">
                               <span class="input-group-btn">
                               <button type="button" class="quantity-left-minus btn btn-number" <?php echo $attributes ?>>
                               <span>-</span>
                               </button>
                               </span>
                               <input type="text" id="quantity" name="quantity" class="form-control input-number quantity_<?php echo $menuItem->menu_item_id ?>" value="<?php echo $cartData['item_quantity'] ?>" min="1" max="100">
                               <span class="input-group-btn">
                               <button type="button" class="quantity-right-plus btn btn-number" <?php echo $attributes ?>>
                               <span>+</span>
                               </button>
                               </span>
                            </div>
                         </div>
                      </span>
                      <span class="amt"><?php echo priceFormat($cartData['item_total_price']+$totalAttrButePrice) ?></span>
                   </div>
                </div>

            <?php
            }
            ?>
            <a href="javascript:void(0)" class="clearCart">Clear Cart</a>
            </div>
         </div>
      </div>
      <div class="cart-show">
         <div class="container">
            <div class="col-xs-6">
               <span class="up-arrow"><a onclick="$('.cart-details').toggle();"><img src="<?php echo asset('public') ?>/images/up-arrow.png"></a></span>
               <span class="order-fix" onclick="$('.cart-details').toggle();">View Cart <b>(<?php echo $totalItems ?>)</b></span>
            </div>
            <div class="col-xs-6 text-right">
               <span class="order-sub">Subtotal <b><?php echo priceFormat($subTotal) ?></b></span>
               <?php
               $delivery_pickup_address['minimumOrderPrice'] = $minimumOrderPrice;
               $delivery_pickup_address['pickDeliveryPrice'] = $pickDeliveryPrice;
               $delivery_pickup_address['orderPrice'] = $subTotal;
               Session::put('delivery_pickup_address', $delivery_pickup_address);
               Session::save();
               if ($subTotal < $minimumOrderPrice) {
                ?>
                <span class="order-cont"><a data-toggle="modal" data-target="#orderMiniPriceCheck">Continue</a></span>
                <?php
               } else {
                ?>
                <span class="order-cont"><a class="checkoutBTN" href="<?php echo url('checkout') ?>">Continue</a></span>
                <?php
               }
               ?>
            </div>
         </div>
      </div>
      <div class="modal fade" id="orderMiniPriceCheck" role="dialog">
         <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
               <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <div class="modal-title">The minimum <?php echo $delivery_pickup_address['order_type'] ?> order for this restaurant is <?php echo priceFormat($delivery_pickup_address['minimumOrderPrice']) ?> </div>
               </div>
               <div class="modal-body" style="padding: 20px;">
                  <div class="row">
                     <div class="col-md-12">
                        <p>The total of your selected items is <?php echo priceFormat($delivery_pickup_address['orderPrice']) ?></p>
                     </div>
                     <div class="col-md-5">
                        <a class="btn btn-info" onclick="window.location.reload();">Choose more items</a>
                     </div>
                     <div class="col-md-7">
                        <a class="btn btn-success checkoutBTN" href="<?php echo url('checkout') ?>">Just charge me an extra <?php echo priceFormat($delivery_pickup_address['minimumOrderPrice']-$delivery_pickup_address['orderPrice']) ?></a>
                     </div>
                     <div class="col-md-12">
                        <p>Calculate after discount but before delivery fees if any</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
        <script type="text/javascript">
            const params = window.location.search;
            var url = "<?php echo url('checkout') ?>"+params;
            $('.checkoutBTN').attr('href', url);
        </script>
    <?php
    }
    return ob_get_clean();
}

function getTodayDeals()
{
    $cartDatas = Session::get ( 'cartData' );
    $delivery_pickup_address = Session::get ( 'delivery_pickup_address' );
    $store_id = array_column($cartDatas, 'store_id');
    $deals = \App\Deals::where(function($query) use($cartDatas, $delivery_pickup_address){
        if ($delivery_pickup_address['order_type'] == 'Pickup') {
            $storeSlug = explode('-', $delivery_pickup_address['store']);
            $store_id = end($storeSlug);
            $location = \App\Stores::where('store_id', $store_id)->get()->pluck('store_postalCode')->first();
        } else {
            $location = $delivery_pickup_address['pincode'];
        }
        if ($location) {
            $locations = \App\StoreDeliveryLocationPrice::where('postal_code', $location)->get()->pluck('store_delivery_location_id');
            $query->whereIn('location', $locations)->orwhere(\DB::raw("location = ''"));
        } else {
            $query->where('location', '');
        }
    })->where('is_deal_auto_apply', 0)->where('store_id', $store_id)->get()->toArray();
    $listedDeals = [];
    foreach ($deals as $deal) {
        $deal = (object)$deal;
        $weekOfDay = maybe_decode($deal->week_of_day);
        $unset = false;
        if (is_array($weekOfDay) && !in_array(date('D'), $weekOfDay)) {
            $unset = true;
        } else if (($deal->start_date && $deal->end_date) && (date('Y-m-d') < $deal->start_date || date('Y-m-d') > $deal->end_date)) {
            $unset = true;
        } else if (($deal->start_time && $deal->end_time) && (date('H:i:s') < date('H:i:s', strtotime($deal->start_time)) || date('H:i:s') > date('H:i:s', strtotime($deal->end_time)))) {
            $unset = true;
        }
        if ($unset == false) {
            $listedDeals[] = $deal;
        }
    }
    return $listedDeals;
}

/*function sendSMSCHeck($content) {
    $ch = curl_init('https://api.smsbroadcast.com.au/api-adv.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec ($ch);
    curl_close ($ch);
    return $output;
}

$username = 'kavish';
$password = 'Maharaja560';
$destination = '+61451700502';
$source    = 'MyCompany';
$text = 'This is our test message.';
$ref = 'abc123';

$content =  'username='.rawurlencode($username).
            '&password='.rawurlencode($password).
            '&to='.rawurlencode($destination).
            '&from='.rawurlencode($source).
            '&message='.rawurlencode($text).
            '&ref='.rawurlencode($ref);

$smsbroadcast_response = sendSMSCHeck($content);
print_r($smsbroadcast_response);
die;*/

function autoApplyDealsCheck()
{
    $delivery_pickup_address = Session::get ( 'delivery_pickup_address' );
    if (!isset($delivery_pickup_address['couponCode']) || (isset($delivery_pickup_address['couponCode']) && empty($delivery_pickup_address['couponCode']))) {
        if ($delivery_pickup_address['order_type'] == 'Pickup' && $deal = \App\Deals::where('is_deal_auto_apply', 1)->where('deal_type', 'POD')->get()->first()) {
            $response = \App\Http\Controllers\Front\CartController::applyAutoDeal($deal);
        } else if ($delivery_pickup_address['order_type'] == 'Delivery' && $deal = \App\Deals::where('is_deal_auto_apply', 1)->where('deal_type', 'DOD')->get()->first()) {
            $response = \App\Http\Controllers\Front\CartController::applyAutoDeal($deal);
        } else {
            $deal = \App\Deals::where('is_deal_auto_apply', 1)->get()->first();
            $response = \App\Http\Controllers\Front\CartController::applyAutoDeal($deal);
        }
    }
}
