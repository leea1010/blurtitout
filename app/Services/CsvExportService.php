<?php
// Đăng ký post type 'therapists'
function create_therapists_post_type()
{
    $labels = array(
        'name' => _x('Therapists', 'Post type general name', 'textdomain'),
        'singular_name' => _x('Therapist', 'Post type singular name', 'textdomain'),
        'menu_name' => _x('Therapists', 'Admin Menu text', 'textdomain'),
        'name_admin_bar' => _x('Therapist', 'Add New on Toolbar', 'textdomain'),
        'add_new' => __('Add New', 'textdomain'),
        'add_new_item' => __('Add New Therapist', 'textdomain'),
        'new_item' => __('New Therapist', 'textdomain'),
        'edit_item' => __('Edit Therapist', 'textdomain'),
        'view_item' => __('View Therapist', 'textdomain'),
        'all_items' => __('All Therapists', 'textdomain'),
        'search_items' => __('Search Therapists', 'textdomain'),
        'parent_item_colon' => __('Parent Therapists:', 'textdomain'),
        'not_found' => __('No therapists found.', 'textdomain'),
        'not_found_in_trash' => __('No therapists found in Trash.', 'textdomain'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'therapists'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies' => array('category'),
    );

    register_post_type('therapists', $args);
}
add_action('init', 'create_therapists_post_type');

// Tạo các taxonomy cho therapists
function create_therapists_taxonomies()
{
    // Taxonomy: Therapist Online Offered
    register_taxonomy('therapist_online_offered', 'therapists', array(
        'labels' => array(
            'name' => 'Online Services',
            'singular_name' => 'Online Service',
            'menu_name' => 'Online Services',
            'add_new_item' => 'Add New Online Service',
            'edit_item' => 'Edit Online Service',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'online-services'),
    ));

    // Taxonomy: States
    register_taxonomy('therapist_state', 'therapists', array(
        'labels' => array(
            'name' => 'States',
            'singular_name' => 'State',
            'menu_name' => 'States',
            'add_new_item' => 'Add New State',
            'edit_item' => 'Edit State',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'states'),
    ));

    // Taxonomy: Specialties
    register_taxonomy('therapist_specialty', 'therapists', array(
        'labels' => array(
            'name' => 'Specialties',
            'singular_name' => 'Specialty',
            'menu_name' => 'Specialties',
            'add_new_item' => 'Add New Specialty',
            'edit_item' => 'Edit Specialty',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'specialties'),
    ));

    // Taxonomy: General Expertise
    register_taxonomy('therapist_general_expertise', 'therapists', array(
        'labels' => array(
            'name' => 'General Expertise',
            'singular_name' => 'Expertise',
            'menu_name' => 'General Expertise',
            'add_new_item' => 'Add New Expertise',
            'edit_item' => 'Edit Expertise',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'expertise'),
    ));

    // Taxonomy: Clinical Approaches
    register_taxonomy('therapist_clinical_approaches', 'therapists', array(
        'labels' => array(
            'name' => 'Clinical Approaches',
            'singular_name' => 'Clinical Approach',
            'menu_name' => 'Clinical Approaches',
            'add_new_item' => 'Add New Clinical Approach',
            'edit_item' => 'Edit Clinical Approach',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'clinical-approaches'),
    ));

    // Taxonomy: Payment Methods
    register_taxonomy('therapist_payment_method', 'therapists', array(
        'labels' => array(
            'name' => 'Payment Methods',
            'singular_name' => 'Payment Method',
            'menu_name' => 'Payment Methods',
            'add_new_item' => 'Add New Payment Method',
            'edit_item' => 'Edit Payment Method',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'payment-methods'),
    ));

    // Taxonomy: Languages
    register_taxonomy('therapist_languages', 'therapists', array(
        'labels' => array(
            'name' => 'Languages',
            'singular_name' => 'Language',
            'menu_name' => 'Languages',
            'add_new_item' => 'Add New Language',
            'edit_item' => 'Edit Language',
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'languages'),
    ));
}
add_action('init', 'create_therapists_taxonomies');

// Thêm meta box cho custom fields của therapists
function add_therapists_meta_boxes()
{
    add_meta_box(
        'therapist_fields_meta_box',
        'Therapist Details',
        'display_therapist_fields_meta_box',
        'therapists',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_therapists_meta_boxes');

function display_therapist_fields_meta_box($post)
{
    $fields = array(
        'avatar',
        'avatar_list',
        'name_prefix',
        'name',
        'title',
        'services_offered',
        'country',
        'office_name',
        'suit',
        'street_address',
        'city',
        'zip_code',
        'gender',
        'email',
        'phone_number',
        'link_to_website',
        'identifies_as_tag',
        'type_of_therapy',
        'about_1',
        'about_2',
        'insurance',
        'fee',
        'license',
        'certification',
        'education',
        'experience',
        'experience_duration',
        'serves_ages',
        'community',
        'faq',
        'source',
    );

    // Các trường sử dụng taxonomy (multiple select)
    $taxonomy_fields = array(
        'online_offered' => 'therapist_online_offered',
        'state' => 'therapist_state',
        'specialty' => 'therapist_specialty',
        'general_expertise' => 'therapist_general_expertise',
        'clinnical_approaches' => 'therapist_clinical_approaches',
        'payment_method' => 'therapist_payment_method',
        'languages' => 'therapist_languages'
    );

    wp_nonce_field(basename(__FILE__), 'therapist_fields_nonce');
    echo '<table class="form-table">';

    // Hiển thị các trường thông thường
    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, '_therapist_' . $field, true);
        $type = ($field === 'created_at' || $field === 'updated_at') ? 'datetime-local' : 'text';

        echo '<tr><th style="text-align:left;"><label for="therapist_' . esc_attr($field) . '">' . esc_html(ucwords(str_replace('_', ' ', $field))) . '</label></th>';

        if ($field === 'avatar_list') {
            echo '<td><textarea style="width:100%;height:60px;" id="therapist_' . esc_attr($field) . '" name="therapist_' . esc_attr($field) . '">' . esc_textarea($value) . '</textarea></td>';
        } else if ($type === 'datetime-local') {
            $val = $value ? date('Y-m-d\TH:i', strtotime($value)) : '';
            echo '<td><input type="datetime-local" id="therapist_' . esc_attr($field) . '" name="therapist_' . esc_attr($field) . '" value="' . esc_attr($val) . '" style="width:100%;" /></td>';
        } else if ($field === 'about_1' || $field === 'about_2' || $field === 'faq' || $field === 'services_offered' || $field === 'avatar') {
            echo '<td><textarea style="width:100%;height:60px;" id="therapist_' . esc_attr($field) . '" name="therapist_' . esc_attr($field) . '">' . esc_textarea($value) . '</textarea></td>';
        } else {
            echo '<td><input type="text" id="therapist_' . esc_attr($field) . '" name="therapist_' . esc_attr($field) . '" value="' . esc_attr($value) . '" style="width:100%;" /></td>';
        }

        echo '</tr>';
    }

    // Hiển thị các trường taxonomy (checkboxes)
    foreach ($taxonomy_fields as $field => $taxonomy) {
        $selected_terms = wp_get_object_terms($post->ID, $taxonomy, array('fields' => 'ids'));
        $all_terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));

        echo '<tr><th style="text-align:left;vertical-align:top;padding-top:10px;"><label for="therapist_' . esc_attr($field) . '">' . esc_html(ucwords(str_replace('_', ' ', $field))) . '</label></th>';
        echo '<td>';
        echo '<div style="max-height:150px;overflow-y:auto;border:1px solid #ddd;padding:10px;background-color:#f9f9f9;">';

        if (!empty($all_terms)) {
            foreach ($all_terms as $term) {
                $checked = in_array($term->term_id, $selected_terms) ? 'checked="checked"' : '';
                echo '<div style="margin-bottom:5px;">';
                echo '<label style="display:inline-block;font-weight:normal;">';
                echo '<input type="checkbox" name="therapist_' . esc_attr($field) . '[]" value="' . esc_attr($term->term_id) . '" ' . $checked . ' style="margin-right:5px;" />';
                echo esc_html($term->name);
                echo '</label>';
                echo '</div>';
            }
        } else {
            echo '<p style="color:#666;font-style:italic;">No terms available. Terms will be created automatically when importing data.</p>';
        }

        echo '</div>';
        echo '<p class="description">Check multiple options as needed</p>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</table>';
}

function save_therapist_fields_meta_box($post_id)
{
    if (!isset($_POST['therapist_fields_nonce']) || !wp_verify_nonce($_POST['therapist_fields_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if ('therapists' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    } else {
        return $post_id;
    }

    // Các trường thông thường
    $fields = array(
        'avatar',
        'avatar_list',
        'name_prefix',
        'name',
        'title',
        'services_offered',
        'country',
        'office_name',
        'suit',
        'street_address',
        'city',
        'zip_code',
        'state_code',  // Thêm state_code như meta field
        'gender',
        'email',
        'phone_number',
        'link_to_website',
        'identifies_as_tag',
        'type_of_therapy',
        'about_1',
        'about_2',
        'insurance',
        'fee',
        'license',
        'certification',
        'education',
        'experience',
        'experience_duration',
        'serves_ages',
        'community',
        'faq',
        'source',
        'created_at',
        'updated_at'
    );

    // Lưu các trường thông thường
    foreach ($fields as $field) {
        if (isset($_POST['therapist_' . $field])) {
            $value = $_POST['therapist_' . $field];
            if ($field === 'created_at' || $field === 'updated_at') {
                $value = sanitize_text_field($value);
            } else {
                $value = is_array($value) ? array_map('sanitize_textarea_field', $value) : sanitize_textarea_field($value);
            }
            update_post_meta($post_id, '_therapist_' . $field, $value);
        } else {
            delete_post_meta($post_id, '_therapist_' . $field);
        }
    }

    // Các trường taxonomy
    $taxonomy_fields = array(
        'online_offered' => 'therapist_online_offered',
        'state' => 'therapist_state',
        'specialty' => 'therapist_specialty',
        'general_expertise' => 'therapist_general_expertise',
        'clinnical_approaches' => 'therapist_clinical_approaches',
        'payment_method' => 'therapist_payment_method',
        'languages' => 'therapist_languages'
    );

    // Lưu các taxonomy
    foreach ($taxonomy_fields as $field => $taxonomy) {
        if (isset($_POST['therapist_' . $field])) {
            $term_ids = array_map('intval', $_POST['therapist_' . $field]);
            wp_set_object_terms($post_id, $term_ids, $taxonomy);
        } else {
            wp_set_object_terms($post_id, array(), $taxonomy);
        }
    }
}
add_action('save_post', 'save_therapist_fields_meta_box');

// Helper function để lấy tất cả therapist fields
function get_therapist_fields($post_id)
{
    $fields = array(
        'avatar',
        'avatar_list',
        'name_prefix',
        'name',
        'title',
        'services_offered',
        'online_offered',
        'country',
        'office_name',
        'suit',
        'street_address',
        'city',
        'zip_code',
        'state',
        'state_code',
        'gender',
        'email',
        'phone_number',
        'link_to_website',
        'identifies_as_tag',
        'specialty',
        'general_expertise',
        'type_of_therapy',
        'clinnical_approaches',
        'about_1',
        'about_2',
        'insurance',
        'payment_method',
        'fee',
        'license',
        'certification',
        'education',
        'experience',
        'experience_duration',
        'serves_ages',
        'community',
        'languages',
        'faq',
        'source',
        'created_at',
        'updated_at'
    );

    $therapist_data = array();
    foreach ($fields as $field) {
        $therapist_data[$field] = get_post_meta($post_id, '_therapist_' . $field, true);
    }

    return $therapist_data;
}

// Function để import dữ liệu mẫu therapists
function import_sample_therapists_data()
{
    // KHÔNG tạo các terms mặc định để tránh tạo nhiều terms không cần thiết
    // create_default_taxonomy_terms();

    // Dữ liệu mẫu (20 therapists)
    $sample_data = array(
        array(
            'title_post' => 'Richard Brown',
            'avatar' => 'https://d3ez4in977nymc.cloudfront.net/avatars/25d4d875baee0b023d5e5e8fa63f78c6.jpg',
            'avatar_list' => 'images/richard_brown_20250807_103413.jpg',
            'name' => 'Richard Brown',
            'title' => 'LCSW-R',
            'services_offered' => 'Online',
            'online_offered' => '["Video","Phone","Live Chat","Messaging"]',
            'country' => 'United States',
            'city' => 'New York, NY',
            'state' => '["NY"]',
            'state_code' => '["072866"]',
            'gender' => 'Male',
            'link_to_website' => 'https://www.betterhelp.com/richard-brown/',
            'identifies_as_tag' => 'Christian-based therapy',
            'specialty' => '["STRESS, ANXIETY","RELATIONSHIP ISSUES","FAMILY CONFLICTS","TRAUMA AND ABUSE","DEPRESSION"]',
            'general_expertise' => '["Avoidant personality","Blended family issues","Dependent personality","Family problems","Infidelity","Life purpose","Veteran and Armed Forces Issues","Obsessions, compulsions, and OCD","Personality disorders","Prejudice and discrimination","Process addiction (porn, exercise, gambling)","Self-harm"]',
            'type_of_therapy' => 'Individual Therapy',
            'clinnical_approaches' => '["Client-Centered Therapy","Cognitive Behavioral Therapy (CBT)"]',
            'about_1' => 'I am licensed in New York with 21 years of professional work experience. I have experience in helping clients with stress and anxiety, relationship issues, family conflicts, & trauma and abuse. I believe in treating everyone with respect, sensitivity, and compassion. I will tailor our dialog and treatment plan to meet your unique and specific needs. Taking the first step to seeking a more fulfilling and happier life takes courage. I am here to support you in that process.',
            'payment_method' => '["Credit Card","PayPal"]',
            'experience' => '26 YRS IN PRACTICE',
            'experience_duration' => '26',
            'languages' => '["English"]',
            'source' => 'BetterHelp',
        ),
        array(
            'title_post' => 'Dr. Sung Ho Kim',
            'avatar' => 'https://d3ez4in977nymc.cloudfront.net/avatars/54c0c564f66a5cc85072cb194e5325dc200548.jpg',
            'avatar_list' => 'images/dr__sung_ho_kim_20250807_103428.jpg',
            'name' => 'Dr. Sung Ho Kim',
            'title' => 'Psychoanalyst',
            'services_offered' => 'Online',
            'online_offered' => '["Video","Phone","Live Chat","Messaging"]',
            'country' => 'United States',
            'city' => 'New York, NY',
            'state' => '["NY"]',
            'state_code' => '["000245"]',
            'gender' => 'Male',
            'link_to_website' => 'https://www.betterhelp.com/sung-ho-kim/',
            'identifies_as_tag' => 'Christian-based therapy',
            'specialty' => '["STRESS, ANXIETY","RELATIONSHIP ISSUES","TRAUMA AND ABUSE","GRIEF","DEPRESSION"]',
            'type_of_therapy' => 'Individual Therapy',
            'clinnical_approaches' => '["Acceptance and Commitment Therapy (ACT)","Attachment-Based Therapy","Client-Centered Therapy","Cognitive Behavioral Therapy (CBT)","Dialectical Behavior Therapy (DBT)","Emotionally-Focused Therapy (EFT)","Existential Therapy","Eye Movement Desensitization and Reprocessing (EMDR)","Gottman Method","Imago Relationship Therapy","Jungian Therapy","Mindfulness Therapy","Motivational Interviewing","Narrative Therapy","Psychodynamic Therapy","Solution-Focused Therapy","Somatic Therapy","Trauma-Focused Therapy","Internal Family Systems","Systemic Therapy"]',
            'payment_method' => '["Credit Card","PayPal"]',
            'experience' => '18 YRS IN PRACTICE',
            'experience_duration' => '18',
            'languages' => '["ENGLISH","KOREAN"]',
            'source' => 'BetterHelp',
        )
        // Có thể thêm thêm dữ liệu mẫu khác tại đây...
    );

    foreach ($sample_data as $therapist) {
        // Tạo post mới
        $post_data = array(
            'post_title' => $therapist['title_post'],
            'post_content' => $therapist['about_1'] ?? '',
            'post_status' => 'publish',
            'post_type' => 'therapists'
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Lưu các custom fields thông thường  
            $taxonomy_fields = array('online_offered', 'state', 'specialty', 'general_expertise', 'clinnical_approaches', 'payment_method', 'languages');

            foreach ($therapist as $key => $value) {
                if ($key !== 'title_post' && !in_array($key, $taxonomy_fields)) {
                    update_post_meta($post_id, '_therapist_' . $key, $value);
                }
            }

            // Xử lý taxonomies
            $taxonomy_mapping = array(
                'online_offered' => 'therapist_online_offered',
                'state' => 'therapist_state',
                'specialty' => 'therapist_specialty',
                'general_expertise' => 'therapist_general_expertise',
                'clinnical_approaches' => 'therapist_clinical_approaches',
                'payment_method' => 'therapist_payment_method',
                'languages' => 'therapist_languages'
            );

            foreach ($taxonomy_mapping as $field => $taxonomy) {
                if (isset($therapist[$field])) {
                    $json_data = json_decode($therapist[$field], true);
                    if (is_array($json_data)) {
                        $term_ids = array();
                        foreach ($json_data as $term_name) {
                            $term = get_term_by('name', $term_name, $taxonomy);
                            if (!$term) {
                                $term = wp_insert_term($term_name, $taxonomy);
                                if (!is_wp_error($term)) {
                                    $term_ids[] = $term['term_id'];
                                }
                            } else {
                                $term_ids[] = $term->term_id;
                            }
                        }
                        wp_set_object_terms($post_id, $term_ids, $taxonomy);
                    }
                }
            }
        }
    }

    return 'Imported sample therapists data successfully!';
}

// Function để tạo các terms mặc định (chỉ sử dụng khi cần thiết)
function create_default_taxonomy_terms()
{
    // Function này chỉ nên được gọi thủ công khi cần tạo dữ liệu mặc định
    // KHÔNG tự động gọi trong import functions

    $default_terms = array(
        'therapist_online_offered' => array('Video', 'Phone', 'Live Chat', 'Messaging', 'Audio'),
        'therapist_state' => array('NY', 'TX', 'CA', 'FL', 'AZ', 'GA', 'NJ', 'NV', 'LA'),
        'therapist_specialty' => array('STRESS, ANXIETY', 'RELATIONSHIP ISSUES', 'FAMILY CONFLICTS', 'TRAUMA AND ABUSE', 'DEPRESSION', 'GRIEF', 'BIPOLAR DISORDER', 'ADHD', 'EATING DISORDERS'),
        'therapist_payment_method' => array('Credit Card', 'PayPal', 'Insurance', 'Cash', 'Check'),
        'therapist_languages' => array('English', 'Spanish', 'French', 'Korean', 'Portuguese', 'Chinese', 'German'),
        'therapist_clinical_approaches' => array(
            'Client-Centered Therapy',
            'Cognitive Behavioral Therapy (CBT)',
            'Dialectical Behavior Therapy (DBT)',
            'Acceptance and Commitment Therapy (ACT)',
            'Attachment-Based Therapy',
            'Emotionally-Focused Therapy (EFT)',
            'Existential Therapy',
            'Eye Movement Desensitization and Reprocessing (EMDR)',
            'Gottman Method',
            'Mindfulness Therapy',
            'Motivational Interviewing',
            'Narrative Therapy',
            'Psychodynamic Therapy',
            'Solution-Focused Therapy',
            'Somatic Therapy',
            'Trauma-Focused Therapy',
            'Internal Family Systems'
        )
    );

    foreach ($default_terms as $taxonomy => $terms) {
        foreach ($terms as $term_name) {
            if (!term_exists($term_name, $taxonomy)) {
                wp_insert_term($term_name, $taxonomy);
            }
        }
    }
}

// Helper function to convert state codes to full state names
function convert_state_code_to_name($state_input)
{
    $state_mapping = array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
        'DC' => 'District of Columbia',
        'PR' => 'Puerto Rico',
        'VI' => 'U.S. Virgin Islands',
        'GU' => 'Guam',
        'AS' => 'American Samoa',
        'MP' => 'Northern Mariana Islands'
    );

    $state_input = strtoupper(trim($state_input));

    // If it's a 2-letter code, convert to full name
    if (strlen($state_input) == 2 && isset($state_mapping[$state_input])) {
        return $state_mapping[$state_input];
    }

    // If it's already a full name, return as is
    return $state_input;
}

// Function để import CSV
function import_therapists_from_csv($file_path)
{
    if (!file_exists($file_path)) {
        return 'CSV file not found!';
    }

    // KHÔNG tạo terms mặc định để tránh tạo nhiều terms không cần thiết
    // create_default_taxonomy_terms();

    $handle = fopen($file_path, 'r');
    if ($handle === FALSE) {
        return 'Cannot open CSV file!';
    }

    // Đọc header
    $headers = fgetcsv($handle);
    if ($headers === FALSE) {
        fclose($handle);
        return 'Cannot read CSV headers!';
    }

    $imported_count = 0;

    // Mapping CSV headers to our fields
    $field_mapping = array(
        'Avatar' => 'avatar',
        'Avatar list' => 'avatar_list',
        'Name_prefix' => 'name_prefix',
        'Name' => 'name',
        'Title' => 'title',
        'Services offered' => 'services_offered',
        'Online_Offered' => 'online_offered',
        'Country' => 'country',
        'Office Name' => 'office_name',
        'Suit' => 'suit',
        'Street address' => 'street_address',
        'City' => 'city',
        'Zip Code' => 'zip_code',
        'State' => 'state',
        'State Code' => 'state_code',
        'Gender' => 'gender',
        'Email' => 'email',
        'Phone Number' => 'phone_number',
        'Link to website' => 'link_to_website',
        'Identifies_as_tag' => 'identifies_as_tag',
        'Specialty' => 'specialty',
        'General Expertise' => 'general_expertise',
        'Type of Therapy' => 'type_of_therapy',
        'Clinnical Approaches' => 'clinnical_approaches',
        'About_1' => 'about_1',
        'About_2' => 'about_2',
        'Insurance' => 'insurance',
        'Payment Method' => 'payment_method',
        'Fee' => 'fee',
        'License' => 'license',
        'Certification' => 'certification',
        'Education' => 'education',
        'Experience' => 'experience',
        'Experience_Duration' => 'experience_duration',
        'Serves Ages' => 'serves_ages',
        'Community' => 'community',
        'Languages' => 'languages',
        'FAQ' => 'faq',
        'Source' => 'source'
    );

    // Đọc từng dòng
    while (($data = fgetcsv($handle)) !== FALSE) {
        if (count($data) < count($headers)) {
            continue; // Skip incomplete rows
        }

        // Tạo array data từ CSV
        $therapist_data = array();
        foreach ($headers as $index => $header) {
            $mapped_field = isset($field_mapping[$header]) ? $field_mapping[$header] : strtolower(str_replace(' ', '_', $header));
            $therapist_data[$mapped_field] = isset($data[$index]) ? $data[$index] : '';
        }

        // Tạo post title từ name
        $post_title = !empty($therapist_data['name']) ? $therapist_data['name'] : 'Therapist ' . ($imported_count + 1);

        // Tạo post
        $post_data = array(
            'post_title' => $post_title,
            'post_content' => $therapist_data['about_1'] ?? '',
            'post_status' => 'publish',
            'post_type' => 'therapists'
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Lưu custom fields
            $taxonomy_fields = array('online_offered', 'state', 'state_code', 'specialty', 'general_expertise', 'clinnical_approaches', 'payment_method', 'languages');

            foreach ($therapist_data as $key => $value) {
                if (!in_array($key, $taxonomy_fields) && $key !== 'id') {
                    update_post_meta($post_id, '_therapist_' . $key, $value);
                }
            }

            // Xử lý taxonomies từ CSV
            $taxonomy_mapping = array(
                'online_offered' => 'therapist_online_offered',
                'state' => 'therapist_state',
                'specialty' => 'therapist_specialty',
                'general_expertise' => 'therapist_general_expertise',
                'clinnical_approaches' => 'therapist_clinical_approaches',
                'payment_method' => 'therapist_payment_method',
                'languages' => 'therapist_languages'
            );

            foreach ($taxonomy_mapping as $field => $taxonomy) {
                if (isset($therapist_data[$field]) && !empty($therapist_data[$field])) {
                    // Clean and split the data
                    $raw_data = $therapist_data[$field];

                    // Remove extra quotes if present
                    $raw_data = trim($raw_data, '"\'');

                    // Split by comma and clean each term
                    $terms = array_map('trim', explode(',', $raw_data));
                    $term_ids = array();

                    foreach ($terms as $term_name) {
                        $term_name = trim($term_name);
                        if (!empty($term_name)) {
                            // Special handling for state - ensure we store the full state name
                            if ($field === 'state') {
                                // Convert state codes to full names if needed
                                $term_name = convert_state_code_to_name($term_name);
                            }

                            $term = get_term_by('name', $term_name, $taxonomy);
                            if (!$term) {
                                $term = wp_insert_term($term_name, $taxonomy);
                                if (!is_wp_error($term)) {
                                    $term_ids[] = $term['term_id'];
                                }
                            } else {
                                $term_ids[] = $term->term_id;
                            }
                        }
                    }

                    if (!empty($term_ids)) {
                        wp_set_object_terms($post_id, $term_ids, $taxonomy);
                    }
                }
            }

            // Special handling for state_code - save as meta instead of taxonomy
            if (isset($therapist_data['state_code']) && !empty($therapist_data['state_code'])) {
                update_post_meta($post_id, '_therapist_state_code', trim($therapist_data['state_code'], '"\''));
            }

            $imported_count++;
        }
    }

    fclose($handle);
    return "Successfully imported {$imported_count} therapists from CSV!";
}

// Function để xóa tất cả therapists và các taxonomy terms liên quan
function delete_all_therapists()
{
    // Xóa tất cả therapists posts
    $therapists = get_posts(array(
        'post_type' => 'therapists',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));

    $deleted_posts = 0;
    foreach ($therapists as $therapist) {
        wp_delete_post($therapist->ID, true);
        $deleted_posts++;
    }

    // Xóa tất cả terms trong các taxonomy liên quan
    $therapist_taxonomies = array(
        'therapist_online_offered',
        'therapist_state',
        'therapist_specialty',
        'therapist_general_expertise',
        'therapist_clinical_approaches',
        'therapist_payment_method',
        'therapist_languages'
    );

    $deleted_terms = 0;
    foreach ($therapist_taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'ids'
        ));

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term_id) {
                wp_delete_term($term_id, $taxonomy);
                $deleted_terms++;
            }
        }
    }

    return "Successfully deleted {$deleted_posts} therapists and {$deleted_terms} taxonomy terms!";
}

// Admin action để import dữ liệu mẫu
function add_therapists_admin_actions()
{
    if (isset($_GET['import_sample_therapists']) && current_user_can('manage_options')) {
        $result = import_sample_therapists_data();
        add_action('admin_notices', function () use ($result) {
            echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
        });
    }

    if (isset($_GET['delete_all_therapists']) && current_user_can('manage_options')) {
        $result = delete_all_therapists();
        add_action('admin_notices', function () use ($result) {
            echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
        });
    }

    // Xử lý CSV upload
    if (isset($_POST['import_csv']) && current_user_can('manage_options')) {
        if (isset($_FILES['therapists_csv']) && $_FILES['therapists_csv']['error'] == 0) {
            $uploaded_file = $_FILES['therapists_csv']['tmp_name'];
            $result = import_therapists_from_csv($uploaded_file);
            add_action('admin_notices', function () use ($result) {
                echo '<div class="notice notice-success"><p>' . $result . '</p></div>';
            });
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>Please select a valid CSV file!</p></div>';
            });
        }
    }
}
add_action('admin_init', 'add_therapists_admin_actions');

// Thêm menu admin để import dữ liệu
function add_therapists_admin_menu()
{
    add_submenu_page(
        'edit.php?post_type=therapists',
        'Import Sample Data',
        'Import Sample Data',
        'manage_options',
        'therapists-import',
        'therapists_import_page'
    );
}
add_action('admin_menu', 'add_therapists_admin_menu');

function therapists_import_page()
{
?>
    <div class="wrap">
        <h1>Therapists Data Management</h1>
        <p>Use the options below to manage therapists data:</p>

        <div style="display: flex; gap: 30px;">
            <div style="flex: 1;">
                <h2>CSV Import</h2>
                <form method="post" enctype="multipart/form-data">
                    <table class="form-table">
                        <tr>
                            <th><label for="therapists_csv">Select CSV File:</label></th>
                            <td>
                                <input type="file" id="therapists_csv" name="therapists_csv" accept=".csv" required />
                                <p class="description">Upload a CSV file with therapist data.</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Import CSV Data', 'primary', 'import_csv'); ?>
                </form>
            </div>

            <div style="flex: 1;">
                <h2>Delete All Data</h2>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=therapists&page=therapists-import&delete_all_therapists=1'); ?>"
                        class="button button-secondary"
                        onclick="return confirm('Are you sure you want to delete ALL therapists and their taxonomy terms? This action cannot be undone!');">
                        Delete All Therapists & Terms
                    </a>
                </p>
                <p class="description" style="color: #d63638;">
                    <strong>Warning:</strong> This will delete all therapist posts AND all taxonomy terms (specialties, states, languages, etc.).
                    Use this to completely reset your data for testing.
                </p>
            </div>
        </div>

    </div>
<?php
}
