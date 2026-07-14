<?php

/**
 * Pakistan provinces → districts, used to drive the checkout address cascade.
 * Tehsil is captured as free text (too many to enumerate reliably). Each
 * province also offers an "Other" path via a manual field on the form, so an
 * unlisted district never blocks an order.
 */
return [
    'provinces' => [
        'Punjab' => [
            'Attock', 'Bahawalnagar', 'Bahawalpur', 'Bhakkar', 'Chakwal', 'Chiniot', 'Dera Ghazi Khan',
            'Faisalabad', 'Gujranwala', 'Gujrat', 'Hafizabad', 'Jhang', 'Jhelum', 'Kasur', 'Khanewal',
            'Khushab', 'Lahore', 'Layyah', 'Lodhran', 'Mandi Bahauddin', 'Mianwali', 'Multan',
            'Muzaffargarh', 'Nankana Sahib', 'Narowal', 'Okara', 'Pakpattan', 'Rahim Yar Khan',
            'Rajanpur', 'Rawalpindi', 'Sahiwal', 'Sargodha', 'Sheikhupura', 'Sialkot', 'Toba Tek Singh',
            'Vehari', 'Wazirabad', 'Other',
        ],
        'Sindh' => [
            'Badin', 'Dadu', 'Ghotki', 'Hyderabad', 'Jacobabad', 'Jamshoro', 'Karachi Central',
            'Karachi East', 'Karachi South', 'Karachi West', 'Kashmore', 'Khairpur', 'Korangi',
            'Larkana', 'Malir', 'Matiari', 'Mirpur Khas', 'Naushahro Feroze', 'Umerkot', 'Sanghar',
            'Shaheed Benazirabad (Nawabshah)', 'Shikarpur', 'Sujawal', 'Sukkur', 'Tando Allahyar',
            'Tando Muhammad Khan', 'Tharparkar', 'Thatta', 'Qambar Shahdadkot', 'Other',
        ],
        'Khyber Pakhtunkhwa' => [
            'Abbottabad', 'Bajaur', 'Bannu', 'Battagram', 'Buner', 'Charsadda', 'Dera Ismail Khan',
            'Hangu', 'Haripur', 'Karak', 'Khyber', 'Kohat', 'Kolai-Palas', 'Kurram', 'Lakki Marwat',
            'Lower Chitral', 'Lower Dir', 'Lower Kohistan', 'Malakand', 'Mansehra', 'Mardan', 'Mohmand',
            'North Waziristan', 'Nowshera', 'Orakzai', 'Peshawar', 'Shangla', 'South Waziristan',
            'Swabi', 'Swat', 'Tank', 'Torghar', 'Upper Chitral', 'Upper Dir', 'Upper Kohistan', 'Other',
        ],
        'Balochistan' => [
            'Awaran', 'Barkhan', 'Chagai', 'Chaman', 'Dera Bugti', 'Duki', 'Gwadar', 'Harnai', 'Jafarabad',
            'Jhal Magsi', 'Kachhi (Bolan)', 'Kalat', 'Kech (Turbat)', 'Kharan', 'Khuzdar', 'Killa Abdullah',
            'Killa Saifullah', 'Kohlu', 'Lasbela', 'Loralai', 'Mastung', 'Musakhel', 'Nasirabad', 'Nushki',
            'Panjgur', 'Pishin', 'Quetta', 'Sherani', 'Sibi', 'Sohbatpur', 'Washuk', 'Zhob', 'Ziarat', 'Other',
        ],
        'Islamabad Capital Territory' => ['Islamabad', 'Other'],
        'Azad Jammu & Kashmir' => [
            'Bagh', 'Bhimber', 'Hattian Bala', 'Haveli', 'Kotli', 'Mirpur', 'Muzaffarabad', 'Neelum',
            'Poonch (Rawalakot)', 'Sudhnoti', 'Other',
        ],
        'Gilgit-Baltistan' => [
            'Astore', 'Diamer', 'Ghanche', 'Ghizer', 'Gilgit', 'Hunza', 'Kharmang', 'Nagar', 'Shigar',
            'Skardu', 'Gupis-Yasin', 'Roundu', 'Darel', 'Tangir', 'Other',
        ],
    ],
];
