<?php
namespace Faker\Provider\ms_MY;
use \Faker\Generator;
class Address extends \Faker\Provider\Address
{
    protected static $addressFormats = array(
        '{{streetAddress}}, {{township}}, {{townState}}',
    );
    protected static $streetAddressFormats = array(
        '{{buildingPrefix}}{{buildingNumber}}, {{streetName}}'
    );
    protected static $buildingPrefix = array(
        '','','','','','',
        'No. ','No. ','No. ',
        'Lot ',
    );
    protected static $buildingNumber = array(
        '%','%','%',
        '%#','%#','%#','%#',
        '%##',
        '%-%',
        '?-##-##',
        '%?-##'
    );
    protected static $streetNameFormats = array(
        '{{streetPrefix}} %',
        '{{streetPrefix}} %/%',
        '{{streetPrefix}} %/%#',
        '{{streetPrefix}} %/%?',
        '{{streetPrefix}} %/%#?',
        '{{streetPrefix}} %?',
        '{{streetPrefix}} %#?',
        '{{streetPrefix}} {{streetSuffix}}',
        '{{streetPrefix}} {{streetSuffix}} %',
        '{{streetPrefix}} {{streetSuffix}} %/%',
        '{{streetPrefix}} {{streetSuffix}} %/%#',
        '{{streetPrefix}} {{streetSuffix}} %/%?',
        '{{streetPrefix}} {{streetSuffix}} %/%#?',
        '{{streetPrefix}} {{streetSuffix}} %?',
        '{{streetPrefix}} {{streetSuffix}} %#?',
    );
    protected static $townshipFormats = array(
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefix}} {{townshipSuffix}}',
        '{{townshipPrefixAbbr}}%',
        '{{townshipPrefixAbbr}}%#',
        '{{townshipPrefixAbbr}}%#?',
    );
    protected static $streetPrefix = array(
        'Jln','Jln',
        'Jalan','Jalan','Jalan',
        'Lorong'
    );
    protected static $streetSuffix = array(
        'Air Itam','Alor','Ampang','Ampang Hilir','Anson','Ariffin',
        'Bangsar','Baru','Bellamy','Birch','Bijih Timah','Bukit Aman','Bukit Bintang','Bukit Petaling','Bukit Tunku',
        'Cantonment','Cenderawasih','Chan Sow Lin','Chow Kit','Cinta','Cochrane','Conlay',
        'D. S. Ramanathan','Damansara','Dang Wangi','Davis','Dewan Bahasa','Dato Abdul Rahman','Dato\'Keramat','Dato\' Maharaja Lela','Doraisamy',
        'Eaton',
        'Faraday',
        'Galloway','Genting Klang','Gereja',
        'Hang Jebat','Hang Kasturi','Hang Lekir','Hang Lekiu','Hang Tuah','Hospital',
        'Imbi','Istana',
        'Jelutong',
        'Kampung Attap','Kebun Bunga','Kedah','Keliling','Kia Peng','Kinabalu','Kuala Kangsar','Kuching',
        'Ledang','Lembah Permai','Loke Yew','Lt. Adnan','Lumba Kuda',
        'Madras','Magazine','Maharajalela','Masjid','Maxwell','Mohana Chandran','Muda',
        'P. Ramlee','Padang Kota Lama','Pahang','Pantai Baharu','Parlimen','Pasar','Pasar Besar','Perak','Perdana','Petaling','Prangin','Pudu','Pudu Lama',
        'Raja','Raja Abdullah','Raja Chulan','Raja Laut','Rakyat','Residensi','Robson',
        'S.P. Seenivasagam','Samarahan 1','Selamat','Sempadan','Sentul','Serian 1','Sasaran','Sin Chee','Sultan Abdul Samad','Sultan Azlan Shah','Sultan Iskandar','Sultan Ismail','Sultan Sulaiman','Sungai Besi','Syed Putra',
        'Tan Cheng Lock','Thambipillay','Tugu','Tuanku Abdul Halim','Tuanku Abdul Rahman','Tun Abdul Razak','Tun Dr Ismail','Tun H S Lee','Tun Ismail','Tun Perak','Tun Razak','Tun Sambanthan',
        'U-Thant','Utama',
        'Vermont','Vivekananda',
        'Wan Kadir','Wesley','Wisma Putra',
        'Yaacob Latif','Yap Ah Loy','Yap Ah Shak','Yap Kwan Seng','Yew',
        'Zaaba','Zainal Abidin'
    );
    protected static $townshipPrefixAbbr = array(
        'SS','Seksyen ','PJS','PJU','USJ ',
    );
    protected static $townshipPrefix = array(
        'Alam','Apartment','Ara',
        'Bandar','Bandar','Bandar','Bandar','Bandar','Bandar',
        'Bandar Bukit','Bandar Seri','Bandar Sri','Bandar Baru','Batu','Bukit',
        'Desa','Damansara',
        'Kampung','Kampung Baru','Kampung Baru','Kondominium','Kota',
        'Laman','Lembah',
        'Medan',
        'Pandan','Pangsapuri','Petaling','Puncak',
        'Seri','Sri',
        'Taman','Taman','Taman','Taman','Taman','Taman',
        'Taman Desa',
    );
    protected static $townshipSuffix = array(
        'Aman','Amanjaya','Anggerik','Angkasa','Antarabangsa','Awan',
        'Bahagia','Bangsar','Baru','Belakong','Bendahara','Bestari','Bintang','Brickfields',
        'Casa','Changkat','Country Heights',
        'Damansara','Damai','Dato Harun','Delima','Duta',
        'Flora',
        'Gembira','Genting',
        'Harmoni','Hartamas',
        'Impian','Indah','Intan',
        'Jasa','Jaya',
        'Keramat','Kerinchi','Kiara','Kinrara','Kuchai',
        'Laksamana',
        'Mahkota','Maluri','Manggis','Maxwell','Medan','Melawati','Menjalara','Meru','Mulia','Mutiara',
        'Pahlawan','Perdana','Pertama','Permai','Pelangi','Petaling','Pinang','Puchong','Puteri','Putra',
        'Rahman','Rahmat','Raya','Razak','Ria',
        'Saujana','Segambut','Selamat','Selatan','Semarak','Sentosa','Seputeh','Setapak','Setia Jaya','Sinar','Sungai Besi','Sungai Buaya','Sungai Long','Suria',
        'Tasik Puteri','Tengah','Timur','Tinggi','Tropika','Tun Hussein Onn','Tun Perak','Tunku',
        'Ulu','Utama','Utara',
        'Wangi',
    );
    protected static $towns = array(
        'johor' => array(
            'Ayer Hitam',
            'Batu Pahat','Bukit Gambir','Bukit Kepong','Bukit Naning',
            'Desaru',
            'Endau',
            'Gelang Patah','Gemas Baharu',
            'Iskandar Puteri',
            'Jementah','Johor Lama','Johor Bahru',
            'Kempas','Kluang','Kota Iskandar','Kota Tinggi','Kukup','Kulai',
            'Labis ','Larkin','Layang-Layang',
            'Mersing','Muar',
            'Pagoh','Paloh','Parit Jawa','Pasir Gudang','Pekan Nanas','Permas Jaya','Pontian Kechil',
            'Renggam',
            'Segamat','Senai','Simpang Renggam','Skudai','Sri Gading',
            'Tangkak','Tebrau',
            'Ulu Tiram',
            'Yong Peng',
        ),
        'kedah' => array(
            'Alor Setar',
            'Baling','Bukit Kayu Hitam',
            'Changlun',
            'Durian Burung',
            'Gurun',
            'Jitra',
            'Kepala Batas','Kuah','Kuala Kedah','Kuala Ketil','Kulim',
            'Langgar','Lunas',
            'Merbok',
            'Padang Serai','Pendang',
            'Serdang','Sintok','Sungai Petani',
            'Tawar, Baling',
            'Yan',
        ),
        'kelantan' => array(
            'Bachok','Bunut Payong',
            'Dabong',
            'Gua Musang',
            'Jeli',
            'Ketereh','Kota Bharu','Kuala Krai',
            'Lojing',
            'Machang',
            'Pasir Mas','Pasir Puteh',
            'Rantau Panjang',
            'Salor',
            'Tok Bali',
            'Wakaf Bharu','Wakaf Che Yeh',
        ),
        'kl' => array(
            'Ampang',
            'Bandar Tasik Selatan','Bandar Tun Razak','Bangsar','Batu','Brickfields','Bukit Bintang','Bukit Jalil','Bukit Tunku',
            'Cheras','Chow Kit',
            'Damansara Town Centre','Dang Wangi','Desa Petaling','Desa Tun Hussein Onn',
            'Jinjang',
            'Kampung Baru','Kampung Kasipillay','Kampung Pandan','Kampung Sungai Penchala','Kepong','KLCC','Kuchai Lama',
            'Lake Gardens','Lembah Pantai',
            'Medan Tuanku','Mid Valley City','Mont Kiara',
            'Pantai Dalam','Pudu',
            'Salak South','Segambut','Semarak','Sentul','Setapak','Setiawangsa','Seputeh','Sri Hartamas','Sri Petaling','Sungai Besi',
            'Taman Desa','Taman Melawati','Taman OUG','Taman Tun Dr Ismail','Taman U-Thant','Taman Wahyu','Titiwangsa','Tun Razak Exchange',
            'Wangsa Maju',
        ),
        'labuan' => array(
            'Batu Manikar',
            'Kiamsam',
            'Layang-Layang',
            'Rancha-Rancha'
        ),
        'melaka' => array(
            'Alor Gajah',
            'Bandaraya Melaka','Batu Berendam','Bukit Beruang','Bukit Katil',
            'Cheng',
            'Durian Tunggal',
            'Hang Tuah Jaya',
            'Jasin',
            'Klebang',
            'Lubuk China',
            'Masjid Tanah',
            'Naning',
            'Pekan Asahan',
            'Ramuan China',
            'Simpang Ampat',
            'Tanjung Bidara','Telok Mas',
            'Umbai',
        ),
        'nsembilan' => array(
            'Ayer Kuning','Ampangan',
            'Bahau','Batang Benar',
            'Chembong',
            'Dangi',
            'Gemas',
            'Juasseh',
            'Kuala Pilah',
            'Labu','Lenggeng','Linggi',
            'Mantin',
            'Nilai',
            'Pajam','Pedas','Pengkalan Kempas','Port Dickson',
            'Rantau','Rompin',
            'Senawang','Seremban','Sungai Gadut',
            'Tampin','Tiroi',
        ),
        'pahang' => array(
            'Bandar Tun Razak','Bentong','Brinchang','Bukit Fraser','Bukit Tinggi',
            'Chendor',
            'Gambang','Genting Highlands','Genting Sempah',
            'Jerantut',
            'Karak','Kemayan','Kota Shahbandar','Kuala Lipis','Kuala Pahang','Kuala Rompin','Kuantan',
            'Lanchang','Lubuk Paku',
            'Maran','Mengkuang','Mentakab',
            'Nenasi',
            'Panching',
            'Pekan','Penor',
            'Raub',
            'Sebertak','Sungai Lembing',
            'Tanah Rata','Tanjung Sepat','Tasik Chini','Temerloh','Teriang','Tringkap',
        ),
        'penang' => array(
            'Air Itam',
            'Balik Pulau','Batu Ferringhi','Batu Kawan','Bayan Lepas','Bukit Mertajam','Butterworth',
            'Gelugor','George Town',
            'Jelutong',
            'Kepala Batas',
            'Nibong Tebal',
            'Permatang Pauh','Pulau Tikus',
            'Simpang Ampat',
            'Tanjung Bungah','Tanjung Tokong',
        ),
        'perak' => array(
            'Ayer Tawar',
            'Bagan Serai','Batu Gajah','Behrang','Bidor','Bukit Gantang','Bukit Merah',
            'Changkat Jering','Chemor','Chenderiang',
            'Damar Laut',
            'Gerik','Gopeng','Gua Tempurung',
            'Hutan Melintang',
            'Ipoh',
            'Jelapang',
            'Kamunting','Kampar','Kuala Kangsar',
            'Lekir','Lenggong','Lumut',
            'Malim Nawar','Manong','Menglembu',
            'Pantai Remis','Parit','Parit Buntar','Pasir Salak','Proton City',
            'Simpang Pulai','Sitiawan','Slim River','Sungai Siput','Sungkai',
            'Taiping','Tambun','Tanjung Malim','Tanjung Rambutan','Tapah','Teluk Intan',
            'Ulu Bernam',
        ),
        'perlis' => array(
            'Arau',
            'Beseri',
            'Chuping',
            'Kaki Bukit','Kangar','Kuala Perlis',
            'Mata Ayer',
            'Padang Besar',
            'Sanglang','Simpang Empat',
            'Wang Kelian',
        ),
        'putrajaya' => array(
            'Precinct 1','Precinct 4','Precinct 5',
            'Precinct 6','Precinct 8','Precinct 10',
            'Precinct 11','Precinct 12','Precinct 13',
            'Precinct 16','Precinct 18','Precinct 19',
        ),
        'sabah' => array(
            'Beaufort','Bingkor',
            'Donggongon',
            'Inanam',
            'Kinabatangan','Kota Belud','Kota Kinabalu','Kuala Penyu','Kimanis','Kundasang',
            'Lahad Datu','Likas','Lok Kawi',
            'Manggatal',
            'Nabawan',
            'Papar','Pitas',
            'Ranau',
            'Sandakan','Sapulut','Semporna','Sepanggar',
            'Tambunan','Tanjung Aru','Tawau','Tenom','Tuaran',
            'Weston',
        ),
        'sarawak' => array(
            'Asajaya',
            'Ba\'kelalan','Bario','Batu Kawa','Batu Niah','Betong','Bintulu',
            'Dalat','Daro',
            'Engkilili',
            'Julau',
            'Kapit','Kota Samarahan','Kuching',
            'Lawas','Limbang','Lubok Antu',
            'Marudi','Matu','Miri',
            'Oya',
            'Pakan',
            'Sadong Jaya','Sematan','Sibu','Siburan','Song','Sri Aman','Sungai Tujoh',
            'Tanjung Kidurong','Tanjung Manis','Tatau',
        ),
        'selangor' => array(
            'Ampang','Assam Jawa',
            'Balakong','Bandar Baru Bangi','Bandar Baru Selayang','Bandar Sunway','Bangi','Banting','Batang Kali','Batu Caves','Bestari Jaya','Bukit Lanjan',
            'Cheras','Cyberjaya',
            'Damansara','Dengkil',
            'Ijok',
            'Jenjarom',
            'Kajang','Kelana Jaya','Klang','Kuala Kubu Bharu','Kuala Selangor','Kuang',
            'Lagong',
            'Morib',
            'Pandamaran','Paya Jaras','Petaling Jaya','Port Klang','Puchong',
            'Rasa','Rawang',
            'Salak Tinggi','Sekinchan','Selayang','Semenyih','Sepang','Serendah','Seri Kembangan','Shah Alam','Subang','Subang Jaya','Sungai Buloh',
            'Tanjung Karang','Tanjung Sepat',
            'Ulu Klang','Ulu Yam',
        ),
        'terengganu' => array(
            'Ajil',
            'Bandar Ketengah Jaya','Bandar Permaisuri','Bukit Besi','Bukit Payong',
            'Chukai',
            'Jerteh',
            'Kampung Raja','Kerteh','Kijal','Kuala Besut','Kuala Berang','Kuala Dungun','Kuala Terengganu',
            'Marang','Merchang',
            'Pasir Raja',
            'Rantau Abang',
            'Teluk Kalung',
            'Wakaf Tapai',
        )
    );
    protected static $states = array(
        'johor' => array(
            'Johor Darul Ta\'zim',
            'Johor'
        ),
        'kedah' => array(
            'Kedah Darul Aman',
            'Kedah'
        ),
        'kelantan' => array(
            'Kelantan Darul Naim',
            'Kelantan'
        ),
        'kl' => array(
            'KL',
            'Kuala Lumpur',
            'WP Kuala Lumpur'
        ),
        'labuan' => array(
            'Labuan'
        ),
        'melaka' => array(
            'Malacca',
            'Melaka'
        ),
        'nsembilan' => array(
            'Negeri Sembilan Darul Khusus',
            'Negeri Sembilan'
        ),
        'pahang' => array(
            'Pahang Darul Makmur',
            'Pahang'
        ),
        'penang' => array(
            'Penang',
            'Pulau Pinang'
        ),
        'perak' => array(
            'Perak Darul Ridzuan',
            'Perak'
        ),
        'perlis' => array(
            'Perlis Indera Kayangan',
            'Perlis'
        ),
        'putrajaya' => array(
            'Putrajaya'
        ),
        'sabah' => array(
            'Sabah'
        ),
        'sarawak' => array(
            'Sarawak'
        ),
        'selangor' => array(
            'Selangor Darul Ehsan',
            'Selangor'
        ),
        'terengganu' => array(
            'Terengganu Darul Iman',
            'Terengganu'
        )
    );
    protected static $country = array(
        'Abkhazia','Afghanistan','Afrika Selatan','Republik Afrika Tengah','Akrotiri dan Dhekelia','Albania','Algeria','Amerika Syarikat','Andorra','Angola','Antigua dan Barbuda','Arab Saudi','Argentina','Armenia','Australia','Austria','Azerbaijan',
        'Bahamas','Bahrain','Bangladesh','Barbados','Belanda','Belarus','Belgium','Belize','Benin','Bhutan','Bolivia','Bonaire','Bosnia dan Herzegovina','Botswana','Brazil','Brunei Darussalam','Bulgaria','Burkina Faso','Burundi',
        'Cameroon','Chad','Chile','Republik Rakyat China','Republik China di Taiwan','Colombia','Comoros','Republik Demokratik Congo','Republik Congo','Kepulauan Cook','Costa Rica','Côte d\'Ivoire (Ivory Coast)','Croatia','Cuba','Curaçao','Cyprus','Republik Turki Cyprus Utara','Republik Czech',
        'Denmark','Djibouti','Dominika','Republik Dominika',
        'Ecuador','El Salvador','Emiriah Arab Bersatu','Eritrea','Estonia',
        'Kepulauan Faroe','Fiji','Filipina','Finland',
        'Gabon','Gambia','Georgia','Ghana','Grenada','Greece (Yunani)','Guatemala','Guinea','Guinea-Bissau','Guinea Khatulistiwa','Guiana Perancis','Guyana',
        'Habsyah (Etiopia)','Haiti','Honduras','Hungary',
        'Iceland','India','Indonesia','Iran','Iraq','Ireland','Israel','Itali',
        'Jamaika','Jepun','Jerman','Jordan',
        'Kanada','Kazakhstan','Kemboja','Kenya','Kiribati','Korea Selatan','Korea Utara','Kosovo','Kuwait','Kyrgyzstan',
        'Laos','Latvia','Lesotho','Liberia','Libya','Liechtenstein','Lithuania','Lubnan','Luxembourg',
        'Macedonia','Madagaskar','Maghribi','Malawi','Malaysia','Maldives','Mali','Malta','Kepulauan Marshall','Mauritania','Mauritius','Mesir','Mexico','Persekutuan Micronesia','Moldova','Monaco','Montenegro','Mongolia','Mozambique','Myanmar',
        'Namibia','Nauru','Nepal','New Zealand','Nicaragua','Niger','Nigeria','Niue','Norway',
        'Oman','Ossetia Selatan',
        'Pakistan','Palau','Palestin','Panama','Papua New Guinea','Paraguay','Perancis','Peru','Poland','Portugal',
        'Qatar',
        'Romania','Russia','Rwanda',
        'Sahara Barat','Saint Kitts dan Nevis','Saint Lucia','Saint Vincent dan Grenadines','Samoa','San Marino','São Tomé dan Príncipe','Scotland','Senegal','Sepanyol','Serbia','Seychelles','Sierra Leone','Singapura','Slovakia','Slovenia','Kepulauan Solomon','Somalia','Somaliland','Sri Lanka','Sudan','Sudan Selatan','Suriname','Swaziland','Sweden','Switzerland','Syria',
        'Tajikistan','Tanjung Verde','Tanzania','Thailand','Timor Leste','Togo','Tonga','Transnistria','Trinidad dan Tobago','Tunisia','Turki','Turkmenistan','Tuvalu',
        'Uganda','Ukraine','United Kingdom','Uruguay','Uzbekistan',
        'Vanuatu','Kota Vatican','Venezuela','Vietnam',
        'Yaman',
        'Zambia','Zimbabwe',
    );
    public static function buildingPrefix()
    {
        return static::randomElement(static::$buildingPrefix);
    }
    public static function buildingNumber()
    {
        return static::toUpper(static::lexify(static::numerify(static::randomElement(static::$buildingNumber))));
    }
    public function streetPrefix()
    {
        $format = static::randomElement(static::$streetPrefix);
        return $this->generator->parse($format);
    }
    public function streetName()
    {
        $format = static::toUpper(static::lexify(static::numerify(static::randomElement(static::$streetNameFormats))));
        return $this->generator->parse($format);
    }
    public function township()
    {
        $format = static::toUpper(static::lexify(static::numerify(static::randomElement(static::$townshipFormats))));
        return $this->generator->parse($format);
    }
    public function townshipPrefixAbbr()
    {
        return static::randomElement(static::$townshipPrefixAbbr);
    }
    public function townshipPrefix()
    {
        return static::randomElement(static::$townshipPrefix);
    }
    public function townshipSuffix()
    {
        return static::randomElement(static::$townshipSuffix);
    }
    public static function postcode($state = null)
    {
        $format = array(
            'perlis' => array( 
                '0' . mt_rand(1000, 2800)
            ),
            'kedah' => array( 
                '0' . mt_rand(5000, 9810)
            ),
            'penang' => array( 
                mt_rand(10000, 14400)
            ),
            'kelantan' => array( 
                mt_rand(15000, 18500)
            ),
            'terengganu' => array( 
                mt_rand(20000, 24300)
            ),
            'pahang' => array( 
                mt_rand(25000, 28800),
                mt_rand(39000, 39200),
                mt_rand(49000, 69000)
            ),
            'perak' => array( 
                mt_rand(30000, 36810)
            ),
            'selangor' => array( 
                mt_rand(40000, 48300),
                mt_rand(63000, 68100)
            ),
            'kl' => array( 
                mt_rand(50000, 60000),
            ),
            'putrajaya' => array( 
                mt_rand(62000, 62988)
            ),
            'nsembilan' => array( 
                mt_rand(70000, 73509)
            ),
            'melaka' => array( 
                mt_rand(75000, 78309)
            ),
            'johor' => array( 
                mt_rand(79000, 86900)
            ),
            'labuan' => array( 
                mt_rand(87000, 87033)
            ),
            'sabah' => array( 
                mt_rand(88000, 91309)
            ),
            'sarawak' => array( 
                mt_rand(93000, 98859)
            )
        );
        $postcode = is_null($state) ? static::randomElement($format) : $format[$state];
        return (string)static::randomElement($postcode);
    }
    public function townState()
    {
        $state = static::randomElement(array_keys(static::$states));
        $postcode = static::postcode($state);
        $town = static::randomElement(static::$towns[$state]);
        $state = static::randomElement(static::$states[$state]);
        return $postcode . ' ' . $town . ', ' . $state;
    }
    public function city()
    {
        $state = static::randomElement(array_keys(static::$towns));
        return static::randomElement(static::$towns[$state]);
    }
    public function state()
    {
        $state = static::randomElement(array_keys(static::$states));
        return static::randomElement(static::$states[$state]);
    }
}