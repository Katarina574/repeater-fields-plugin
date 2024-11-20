<?php
/*
Plugin Name: Universal Repeater Plugin
Description: A flexible repeater plugin for adding dynamic fields, subfields, and solutions.
Version: 1.0
Author: Katarina Pavlovic
Text Domain: universal-repeater
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'urp_create_plugin_menu');

function urp_create_plugin_menu() {
    add_menu_page(
        'Universal Repeater',
        'Universal Repeater',
        'manage_options',
        'universal-repeater',
        'urp_admin_page',
        'dashicons-welcome-widgets-menus',
        26
    );
}

function urp_admin_page() {
    $post_types = get_post_types(['public' => true], 'objects'); // Dohvati sve javne post type-ove
    ?>
    <div class="wrap container">
        <h1 class="my-4">Universal Repeater Plugin</h1>
        <form id="urp-repeater-form" method="POST" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="save_repeater">
            <?php wp_nonce_field('urp_save_repeater', 'urp_nonce'); ?>

            <div id="group-container" class="mb-3">
                <div class="group card mb-4">
                    <div class="card-header">
                        <input type="text" class="form-control mb-2" name="groups[0][name]" placeholder="Naziv Grupe" required>

                        <select class="form-control mb-2" name="groups[0][post_type]" required>
                            <option value="">Odaberi Post Type</option>
                            <?php foreach ($post_types as $post_type): ?>
                                <option value="<?php echo esc_attr($post_type->name); ?>">
                                    <?php echo esc_html($post_type->labels->singular_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="button" class="btn btn-secondary add-field">Dodaj Polje</button>
                    </div>
                    <div class="card-body field-container">
                        <!-- Polja i podpolja će se dinamički dodavati ovde -->
                    </div>
                </div>
            </div>

            <button type="button" id="add-group" class="btn btn-primary">Dodaj Grupu</button>
            <input type="submit" value="Sačuvaj" class="btn btn-success mt-3">
        </form>

    </div>
    <?php
}



add_action('admin_enqueue_scripts', 'urp_enqueue_scripts');

function urp_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_universal-repeater') {
        return;
    }
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true);
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    wp_enqueue_script('urp-script', plugin_dir_url(__FILE__) . 'assets/js/urp-script.js', array('jquery'), '1.0', true);
}


// Funkcija koja se pokreće prilikom aktivacije plugina
register_activation_hook(__FILE__, 'urp_create_table');
//CUVANJE VREDMOSTI ISPOD
function urp_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'universal_repeater';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "
CREATE TABLE wpym_repeater_groups (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    group_name VARCHAR(255) NOT NULL,
    post_type VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);


CREATE TABLE wpym_repeater_fields (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    group_id BIGINT(20) UNSIGNED NOT NULL,
    field_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (group_id) REFERENCES wpym_repeater_groups(id) ON DELETE CASCADE
);


CREATE TABLE wpym_repeater_subfields (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    field_id BIGINT(20) UNSIGNED NOT NULL,
    subfield_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (field_id) REFERENCES wpym_repeater_fields(id) ON DELETE CASCADE
);
";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


add_action('admin_post_save_repeater', 'urp_save_repeater_data');

function urp_save_repeater_data() {
    global $wpdb;

    // Provera nonce vrednosti
    if (!isset($_POST['urp_nonce']) || !wp_verify_nonce($_POST['urp_nonce'], 'urp_save_repeater')) {
        wp_die('Neispravna verifikacija.');
    }

    //test sa je prosledjeno iz forme
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    exit;

    $group_table = $wpdb->prefix . 'repeater_groups';
    $field_table = $wpdb->prefix . 'repeater_fields';
    $subfield_table = $wpdb->prefix . 'repeater_subfields';
    $group_name = sanitize_text_field(trim($group['name']));
    $post_type = sanitize_text_field(trim($group['post_type']));
    // Provera da li su prosleđeni podaci za grupe
    if (isset($_POST['groups']) && is_array($_POST['groups'])) {
        foreach ($_POST['groups'] as $group) {
                echo '<pre>';
    print_r('Group Name after trim: ' . $group_name);
    print_r('Post Type after trim: ' . $post_type);
    echo '</pre>';
    exit;

            if (empty($group['name']) || empty($group['post_type'])) {
                wp_die('Naziv grupe i post type su obavezni.');
            }

            $group_name = sanitize_text_field($group['name']);
            $post_type = sanitize_text_field($group['post_type']);

            // Snimanje grupe
            $insert_group = $wpdb->insert($group_table, [
                'group_name' => $group_name,
                'post_type'  => $post_type,
            ]);

            if ($insert_group === false) {
                wp_die('Greška prilikom snimanja grupe.');
            }

            $group_id = $wpdb->insert_id;

            // Provera i snimanje polja za grupu
            if (isset($group['fields']) && is_array($group['fields'])) {
                foreach ($group['fields'] as $field) {
                    if (empty($field['name'])) {
                        wp_die('Ime polja je obavezno.');
                    }

                    $field_name = sanitize_text_field($field['name']);

                    $insert_field = $wpdb->insert($field_table, [
                        'group_id'   => $group_id,
                        'field_name' => $field_name,
                    ]);

                    if ($insert_field === false) {
                        wp_die('Greška prilikom snimanja polja.');
                    }

                    $field_id = $wpdb->insert_id;

                    // Provera i snimanje podpolja
                    if (isset($field['subfields']) && is_array($field['subfields'])) {
                        foreach ($field['subfields'] as $subfield) {
                            if (empty($subfield)) {
                                wp_die('Ime podpolja je obavezno.');
                            }

                            $subfield_name = sanitize_text_field($subfield);

                            $insert_subfield = $wpdb->insert($subfield_table, [
                                'field_id'      => $field_id,
                                'subfield_name' => $subfield_name,
                            ]);

                            if ($insert_subfield === false) {
                                wp_die('Greška prilikom snimanja podpolja.');
                            }
                        }
                    }
                }
            }
        }
    } else {
        wp_die('Nema validnih podataka za čuvanje.');
    }

    // Redirektovanje na admin stranicu nakon uspešnog snimanja
    wp_redirect(admin_url('admin.php?page=universal-repeater'));
    exit;
}





function urp_display_repeater_data() {
    global $wpdb;

    $group_table = $wpdb->prefix . 'repeater_groups';
    $field_table = $wpdb->prefix . 'repeater_fields';
    $subfield_table = $wpdb->prefix . 'repeater_subfields';

    $groups = $wpdb->get_results("SELECT * FROM $group_table");

    foreach ($groups as $group) {
        echo "<h2>Grupa: {$group->group_name}</h2>";

        $fields = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $field_table WHERE group_id = %d",
            $group->id
        ));

        foreach ($fields as $field) {
            echo "<h3>Polje: {$field->field_name}</h3>";

            $subfields = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $subfield_table WHERE field_id = %d",
                $field->id
            ));

            foreach ($subfields as $subfield) {
                echo "<p>Podpolje: {$subfield->subfield_name}</p>";
            }
        }
    }
}


add_action('add_meta_boxes', 'urp_add_custom_meta_box');

function urp_add_custom_meta_box() {
    global $wpdb;
    $group_table = $wpdb->prefix . 'repeater_groups';

    // Dohvati sve grupe za trenutni post type
    $post_type = get_post_type();
    $groups = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $group_table WHERE post_type = %s",
        $post_type
    ));

    foreach ($groups as $group) {
        add_meta_box(
            'urp_group_' . $group->id,
            $group->group_name,
            'urp_render_meta_box',
            $post_type,
            'normal',
            'high',
            ['group_id' => $group->id]
        );
    }
}

function urp_render_meta_box($post, $meta_box) {
    global $wpdb;
    $field_table = $wpdb->prefix . 'repeater_fields';
    $subfield_table = $wpdb->prefix . 'repeater_subfields';

    $group_id = $meta_box['args']['group_id'];
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $field_table WHERE group_id = %d",
        $group_id
    ));

    echo '<table class="form-table">';
    foreach ($fields as $field) {
        echo '<tr>';
        echo '<th><label>' . esc_html($field->field_name) . '</label></th>';
        echo '<td><input type="text" name="urp_field_' . $field->id . '" value="" class="regular-text"></td>';
        echo '</tr>';
    }
    echo '</table>';
}
