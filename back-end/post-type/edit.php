<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add metabox for influactive forms
add_action('add_meta_boxes', 'influactive_form_add_metaboxes');
function influactive_form_add_metaboxes(): void
{
    add_meta_box('influactive_form_metabox', __('Influactive Form', 'influactive-forms'), 'influactive_form_metabox', 'influactive-forms');
}

function influactive_form_metabox($post): void
{
    ?>
    <?php influactive_form_shortcode($post); ?>
    <div class="tabs">
        <ul class="tab-links">
            <li class="active"><a href="#fields"><?= __('Form Fields', 'influactive-forms') ?></a></li>
            <li><a href="#style"><?= __('Form Style', 'influactive-forms') ?></a></li>
            <li><a href="#email"><?= __('Email Layout', 'influactive-forms') ?></a></li>
            <li><a href="#preview"><?= __('Form preview', 'influactive-forms') ?></a></li>
        </ul>

        <div class="tab-content">
            <div id="fields" class="tab active">
                <!-- Form fields content -->
                <h2><?= __('Form Fields', 'influactive-forms') ?></h2>
                <?php influactive_form_fields_listing($post); ?>
            </div>
            <div id="style" class="tab">
                <!-- Email style content -->
                <h2><?= __('Form Style', 'influactive-forms') ?></h2>
                <?php influactive_form_email_style($post); ?>
            </div>
            <div id="email" class="tab">
                <!-- Email style content -->
                <h2><?= __('Email Layout', 'influactive-forms') ?></h2>
                <?php influactive_form_email_layout($post); ?>
            </div>
            <div id="preview" class="tab">
                <!-- Form preview content -->
                <h2><?= __('Form preview', 'influactive-forms') ?></h2>
                <?php do_shortcode('[influactive_form id="' . $post->ID . '"]'); ?>
        </div>
    </div>
    <?php
}

function influactive_form_shortcode($post): void
{
    echo '<code>[influactive_form id="' . $post->ID . '"]</code>';
}

function influactive_form_fields_listing($post): void
{
    $fields = get_post_meta($post->ID, '_influactive_form_fields', true);

    echo '<div id="influactive_form_fields_container">';

    if (is_array($fields)) {
        foreach ($fields as $key => $field) {
            echo '<div class="influactive_form_field">';
            echo '<p><label>' . __('Type', 'influactive-forms') . ' <select name="influactive_form_fields[' . (int)$key . '][type]" class="field_type">';
            echo '<option value="text" ' . (isset($field['type']) && $field['type'] === 'text' ? 'selected' : '') . '>' . __('Text', 'influactive-forms') . '</option>';
            echo '<option value="email" ' . (isset($field['type']) && $field['type'] === 'email' ? 'selected' : '') . '>' . __('Email', 'influactive-forms') . '</option>';
            echo '<option value="number" ' . (isset($field['type']) && $field['type'] === 'number' ? 'selected' : '') . '>' . __('Number', 'influactive-forms') . '</option>';
            echo '<option value="textarea" ' . (isset($field['type']) && $field['type'] === 'textarea' ? 'selected' : '') . '>' . __('Textarea', 'influactive-forms') . '</option>';
            echo '<option value="select" ' . (isset($field['type']) && $field['type'] === 'select' ? 'selected' : '') . '>' . __('Select', 'influactive-forms') . '</option>';
            echo '<option value="gdpr" ' . (isset($field['type']) && $field['type'] === 'gdpr' ? 'selected' : '') . '>' . __('GDPR', 'influactive-forms') . '</option>';
            echo '<option value="free_text" ' . (isset($field['type']) && $field['type'] === 'free_text' ? 'selected' : '') . '>' . __('Free text', 'influactive-forms') . '</option>';
            echo '</select></label>';
            if (isset($field['type']) && $field['type'] === 'gdpr') {
                echo '<label>Text <input type="text" name="influactive_form_fields[' . (int)$key . '][label]" value="' . esc_attr($field['label']) . '" class="influactive_form_fields_label"></label> ';
                echo '<label><input type="hidden" name="influactive_form_fields[' . (int)$key . '][name]" value="gdpr" class="influactive_form_fields_name"></label>';
            } else if (isset($field['type']) && $field['type'] === 'free_text') {
                // Wysiwyg field
                wp_editor($field['label'], 'influactive_form_fields_' . $key . '_label', array(
                    'textarea_name' => 'influactive_form_fields[' . (int)$key . '][label]',
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => true,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,underline,link,unlink,undo,redo,formatselect,backcolor,alignleft,alignecenter,alignright,alignjustify,bullist,numlist,outdent,indent,removeformat',
                    ),
                    'editor_class' => 'influactive_form_fields_label wysiwyg-editor'
                ));
                echo '<label><input type="hidden" name="influactive_form_fields[' . (int)$key . '][name]" value="free_text" class="influactive_form_fields_name"></label>';
            } else if (isset($field['type']) && $field['type'] === 'select') {
                echo '<label>Label <input type="text" name="influactive_form_fields[' . (int)$key . '][label]" value="' . esc_attr($field['label']) . '" class="influactive_form_fields_label"></label> ';
                echo '<label>Name <input type="text" name="influactive_form_fields[' . (int)$key . '][name]" value="' . strtolower(esc_attr($field['name'])) . '" class="influactive_form_fields_name"></label> ';
                echo '<div class="options_container">';
                if (is_array($field['options'])) {
                    foreach ($field['options'] as $option_index => $option) {
                        echo '<p class="option-field" data-index="' . $option_index . '">';
                        echo '<label>' . __('Option Label', 'influactive-forms');
                        echo '<input type="text" class="option-label" name="influactive_form_fields[' . (int)$key . '][options][' . (int)$option_index . '][label]" value="' . esc_attr($option['label']) . '">';
                        echo '</label>';
                        echo '<label>' . __('Option Value', 'influactive-forms');
                        echo '<input type="text" class="option-value" name="influactive_form_fields[' . (int)$key . '][options][' . (int)$option_index . '][value]" value="' . esc_attr($option['value']) . '">';
                        echo '</label>';
                        echo '<a href="#" class="remove_option">' . __('Remove option', 'influactive-forms') . '</a>';
                        echo '</p>';
                    }
                }
                echo '</div>';
                echo '<p><a href="#" class="add_option">' . __('Add option', 'influactive-forms') . '</a></p>';
            } else if (isset($field['type'])) {
                echo '<label>Label <input type="text" name="influactive_form_fields[' . (int)$key . '][label]" value="' . esc_attr($field['label']) . '" class="influactive_form_fields_label"></label> ';
                echo '<label>Name <input type="text" name="influactive_form_fields[' . (int)$key . '][name]" value="' . strtolower(esc_attr($field['name'])) . '" class="influactive_form_fields_name"></label> ';
            }

            echo '<input type="hidden" name="influactive_form_fields[' . (int)$key . '][order]" value="' . (int)$key . '" class="influactive_form_fields_order">';
            echo '<a href="#" class="remove_field">' . __('Remove the field', 'influactive-forms') . '</a></p>';
            echo '</div>';
        }
    }

    echo '</div>';

    echo '<p><a href="#" id="add_field">' . __('Add Field', 'influactive-forms') . '</a></p>';
}

function influactive_form_email_style($post): void
{
    $email_style = get_post_meta($post->ID, '_influactive_form_email_style', true);
	$email_style['form']['border_style'] ?? $email_style['form']['border_style'] = 'solid';
	$email_style['label']['font_weight'] ?? $email_style['label']['font_weight'] = 'normal';
    $email_style['input']['font_weight'] ?? $email_style['input']['font_weight'] = 'normal';
	$email_style['input']['border_style'] ?? $email_style['input']['border_style'] = 'solid';
    $email_style['submit']['font_weight'] ?? $email_style['submit']['font_weight'] = 'normal';
    $email_style['submit']['border_style'] ?? $email_style['submit']['border_style'] = 'solid';
    ?>
    <div id="influactive_form_style_container">
        <p>
            <label>
                <?= __('Form Background color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[form][background_color]"
                       value="<?= $email_style['form']['background_color'] ?? '#f6f6f6' ?>">
            </label>
            <label>
                <?= __('Form Padding', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[form][padding]"
                       value="<?= esc_attr($email_style['form']['padding'] ?? '20px') ?>">
            </label>
            <label>
                <?= __('Form Border width', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[form][border_width]"
                       value="<?= esc_attr($email_style['form']['border_width'] ?? '1px') ?>">
            </label>
            <label>
                 <?= __('Form Border style', 'influactive-forms') ?>
                <select name="influactive_form_email_style[form][border_style]">
                    <option value="solid" <?= $email_style['form']['border_style'] === "solid" ? "selected" : "" ?>><?= __('Solid', 'influactive-forms') ?>
                    </option>
                    <option value="dashed" <?= $email_style['form']['border_style'] === "dashed" ? "selected" : "" ?>><?= __('Dashed', 'influactive-forms') ?>
                    </option>
                    <option value="dotted" <?= $email_style['form']['border_style'] === "dotted" ? "selected" : "" ?>><?= __('Dotted', 'influactive-forms') ?>
                    </option>
                    <option value="double" <?= $email_style['form']['border_style'] === "double" ? "selected" : "" ?>><?= __('Double', 'influactive-forms') ?>
                    </option>
                    <option value="groove" <?= $email_style['form']['border_style'] === "groove" ? "selected" : "" ?>><?= __('Groove', 'influactive-forms') ?>
                    </option>
                    <option value="ridge" <?= $email_style['form']['border_style'] === "ridge" ? "selected" : "" ?>><?= __('Ridge', 'influactive-forms') ?>
                    </option>
                    <option value="inset" <?= $email_style['form']['border_style'] === "inset" ? "selected" : "" ?>><?= __('Inset', 'influactive-forms') ?>
                    </option>
                    <option value="outset" <?= $email_style['form']['border_style'] === "outset" ? "selected" : "" ?>><?= __('Outset', 'influactive-forms') ?>
                    </option>
                    <option value="none" <?= $email_style['form']['border_style'] === "none" ? "selected" : "" ?>><?= __('None', 'influactive-forms') ?></option>
                    <option value="hidden" <?= $email_style['form']['border_style'] === "hidden" ? "selected" : "" ?>><?= __('Hidden', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Form Border color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[form][border_color]"
                       value="<?= esc_attr($email_style['form']['border_color'] ?? '#cccccc') ?>">
            </label>
        </p>
        <p>
            <label>
                 <?= __('Label Font family', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[label][font_family]"
                       value="<?= esc_attr($email_style['label']['font_family'] ?? 'Arial, Helvetica, sans-serif') ?>">
            </label>
            <label>
                <?= __('Label font size', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[label][font_size]"
                       value="<?= esc_attr($email_style['label']['font_size'] ?? '14px') ?>">
            </label>
            <label>
                <?= __('Label font color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[label][font_color]"
                       value="<?= esc_attr($email_style['label']['font_color'] ?? '#333333') ?>">
            </label>
            <label>
                <?= __('Label font weight', 'influactive-forms') ?>
                <select name="influactive_form_email_style[label][font_weight]">
                    <option value="normal" <?= $email_style['label']['font_weight'] === "normal" ? "selected" : "" ?>>
                        <?= __('Normal', 'influactive-forms') ?>
                    </option>
                    <option value="bold" <?= $email_style['label']['font_weight'] === "bold" ? "selected" : "" ?>>
                        <?= __('Bold', 'influactive-forms') ?>
                    </option>
                    <option value="bolder" <?= $email_style['label']['font_weight'] === "bolder" ? "selected" : "" ?>>
                        <?= __('Bolder', 'influactive-forms') ?>
                    </option>
                    <option value="medium" <?= $email_style['label']['font_weight'] === "medium" ? "selected" : "" ?>>
                        <?= __('Medium', 'influactive-forms') ?>
                    </option>
                    <option value="lighter" <?= $email_style['label']['font_weight'] === "lighter" ? "selected" : "" ?>>
                        <?= __('Lighter', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Label line height', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[label][line_height]"
                       value="<?= esc_attr($email_style['label']['line_height'] ?? '1.5') ?>">
            </label>
        </p>
        <p>
            <label>
                <?= __('Input font family', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][font_family]"
                       value="<?= esc_attr($email_style['input']['font_family'] ?? 'Arial, Helvetica, sans-serif') ?>">
            </label>
            <label>
                <?= __('Input font size', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][font_size]"
                       value="<?= esc_attr($email_style['input']['font_size'] ?? '14px') ?>">
            </label>
            <label>
                 <?= __('Input font color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[input][font_color]"
                       value="<?= esc_attr($email_style['input']['font_color'] ?? '#333333') ?>">
            </label>
            <label>
                <?= __('Input font weight', 'influactive-forms') ?>
                <select name="influactive_form_email_style[input][font_weight]">
                    <option value="normal" <?= $email_style['input']['font_weight'] === "normal" ? "selected" : "" ?>>
                        <?= __('Normal', 'influactive-forms') ?>
                    </option>
                    <option value="bold" <?= $email_style['input']['font_weight'] === "bold" ? "selected" : "" ?>>
                        <?= __('Bold', 'influactive-forms') ?>
                    </option>
                    <option value="bolder" <?= $email_style['input']['font_weight'] === "bolder" ? "selected" : "" ?>>
                        <?= __('Bolder', 'influactive-forms') ?>
                    </option>
                    <option value="medium" <?= $email_style['input']['font_weight'] === "medium" ? "selected" : "" ?>>
                        <?= __('Medium', 'influactive-forms') ?>
                    </option>
                    <option value="lighter" <?= $email_style['input']['font_weight'] === "lighter" ? "selected" : "" ?>>
                        <?= __('Lighter', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Input line height', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][line_height]"
                       value="<?= esc_attr($email_style['input']['line_height'] ?? '1.5') ?>">
            </label>
            <label>
                <?= __('Input background color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[input][background_color]"
                       value="<?= esc_attr($email_style['input']['background_color'] ?? '#ffffff') ?>">
            </label>
            <label>
                 <?= __('Input border width', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][border_width]"
                       value="<?= esc_attr($email_style['input']['border_width'] ?? '1px') ?>">
            </label>
            <label>
                <?= __('Input border style', 'influactive-forms') ?>
                <select name="influactive_form_email_style[input][border_style]">
                    <option value="solid" <?= $email_style['input']['border_style'] === "solid" ? "selected" : "" ?>>
                        <?= __('Solid', 'influactive-forms') ?>
                    </option>
                    <option value="dashed" <?= $email_style['input']['border_style'] === "dashed" ? "selected" : "" ?>>
                        <?= __('Dashed', 'influactive-forms') ?>
                    </option>
                    <option value="dotted" <?= $email_style['input']['border_style'] === "dotted" ? "selected" : "" ?>>
                        <?= __('Dotted', 'influactive-forms') ?>
                    </option>
                    <option value="double" <?= $email_style['input']['border_style'] === "double" ? "selected" : "" ?>>
                        <?= __('Double', 'influactive-forms') ?>
                    </option>
                    <option value="groove" <?= $email_style['input']['border_style'] === "groove" ? "selected" : "" ?>>
                        <?= __('Groove', 'influactive-forms') ?>
                    </option>
                    <option value="ridge" <?= $email_style['input']['border_style'] === "ridge" ? "selected" : "" ?>>
                        <?= __('Ridge', 'influactive-forms') ?>
                    </option>
                    <option value="inset" <?= $email_style['input']['border_style'] === "inset" ? "selected" : "" ?>>
                        <?= __('Inset', 'influactive-forms') ?>
                    </option>
                    <option value="outset" <?= $email_style['input']['border_style'] === "outset" ? "selected" : "" ?>>
                        <?= __('Outset', 'influactive-forms') ?>
                    </option>
                    <option value="hidden" <?= $email_style['input']['border_style'] === "hidden" ? "selected" : "" ?>>
                        <?= __('Hidden', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Input border color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[input][border_color]"
                       value="<?= esc_attr($email_style['input']['border_color'] ?? '#cccccc') ?>">
            </label>
            <label>
                <?= __('Input border radius', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][border_radius]"
                       value="<?= esc_attr($email_style['input']['border_radius'] ?? '0') ?>">
            </label>
            <label>
                <?= __('Input padding', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[input][padding]"
                       value="<?= esc_attr($email_style['input']['padding'] ?? '10px') ?>">
            </label>
        </p>
        <p>
            <label>
                 <?= __('Submit font family', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][font_family]"
                       value="<?= esc_attr($email_style['submit']['font_family'] ?? 'Arial, Helvetica, sans-serif') ?>">
            </label>
            <label>
                <?= __('Submit font size', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][font_size]"
                       value="<?= esc_attr($email_style['submit']['font_size'] ?? '14px') ?>">
            </label>
            <label>
                 <?= __('Submit font color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[submit][font_color]"
                       value="<?= esc_attr($email_style['submit']['font_color'] ?? '#ffffff') ?>">
            </label>
            <label>
                <?= __('Submit font hover color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[submit][font_hover_color]"
                       value="<?= esc_attr($email_style['submit']['font_hover_color'] ?? '#ffffff') ?>">
            </label>
            <label>
                <?= __('Submit font weight', 'influactive-forms') ?>
                <select name="influactive_form_email_style[submit][font_weight]">
                    <option value="normal" <?= $email_style['submit']['font_weight'] === "normal" ? "selected" : "" ?>>
                        <?= __('Normal', 'influactive-forms') ?>
                    </option>
                    <option value="bold" <?= $email_style['submit']['font_weight'] === "bold" ? "selected" : "" ?>>
                        <?= __('Bold', 'influactive-forms') ?>
                    </option>
                    <option value="bolder" <?= $email_style['submit']['font_weight'] === "bolder" ? "selected" : "" ?>>
                        <?= __('Bolder', 'influactive-forms') ?>
                    </option>
                    <option value="lighter" <?= $email_style['submit']['font_weight'] === "lighter" ? "selected" : "" ?>>
                        <?= __('Lighter', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Submit line height', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][line_height]"
                       value="<?= esc_attr($email_style['submit']['line_height'] ?? '1.5') ?>">
            </label>
            <label>
                <?= __('Submit background color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[submit][background_color]"
                       value="<?= esc_attr($email_style['submit']['background_color'] ?? '#333333') ?>">
            </label>
            <label>
                 <?= __('Submit background hover color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[submit][background_hover_color]"
                       value="<?= esc_attr($email_style['submit']['background_hover_color'] ?? '#333333') ?>">
            </label>
            <label>
                <?= __('Submit border color', 'influactive-forms') ?>
                <input type="color" name="influactive_form_email_style[submit][border_color]"
                       value="<?= esc_attr($email_style['submit']['border_color'] ?? '#333333') ?>">
            </label>
            <label>
                <?= __('Submit border style', 'influactive-forms') ?>
                <select name="influactive_form_email_style[submit][border_style]">
                    <option value="solid" <?= $email_style['submit']['border_style'] === "solid" ? "selected" : "" ?>>
                        <?= __('Solid', 'influactive-forms') ?>
                    </option>
                    <option value="dashed" <?= $email_style['submit']['border_style'] === "dashed" ? "selected" : "" ?>>
                        <?= __('Dashed', 'influactive-forms') ?>
                    </option>
                    <option value="dotted" <?= $email_style['submit']['border_style'] === "dotted" ? "selected" : "" ?>>
                        <?= __('Dotted', 'influactive-forms') ?>
                    </option>
                    <option value="double" <?= $email_style['submit']['border_style'] === "double" ? "selected" : "" ?>>
                        <?= __('Double', 'influactive-forms') ?>
                    </option>
                    <option value="groove" <?= $email_style['submit']['border_style'] === "groove" ? "selected" : "" ?>>
                        <?= __('Groove', 'influactive-forms') ?>
                    </option>
                    <option value="ridge" <?= $email_style['submit']['border_style'] === "ridge" ? "selected" : "" ?>>
                        <?= __('Ridge', 'influactive-forms') ?>
                    </option>
                    <option value="inset" <?= $email_style['submit']['border_style'] === "inset" ? "selected" : "" ?>>
                        <?= __('Inset', 'influactive-forms') ?>
                    </option>
                    <option value="outset" <?= $email_style['submit']['border_style'] === "outset" ? "selected" : "" ?>>
                        <?= __('Outset', 'influactive-forms') ?>
                    </option>
                    <option value="hidden" <?= $email_style['submit']['border_style'] === "hidden" ? "selected" : "" ?>>
                        <?= __('Hidden', 'influactive-forms') ?>
                    </option>
                </select>
            </label>
            <label>
                <?= __('Submit border width', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][border_width]"
                       value="<?= esc_attr($email_style['submit']['border_width'] ?? '1px') ?>">
            </label>
            <label>
                <?= __('Submit border radius', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][border_radius]"
                       value="<?= esc_attr($email_style['submit']['border_radius'] ?? '0') ?>">
            </label>
            <label>
                 <?= __('Submit padding', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_style[submit][padding]"
                       value="<?= esc_attr($email_style['submit']['padding'] ?? '10px 20px') ?>">
            </label>
        </p>
    </div>
    <?php
}

function influactive_form_email_layout($post): void
{
    $email_layout = get_post_meta($post->ID, '_influactive_form_email_layout', true);
    ?>
    <div id="influactive_form_layout_container">
        <p>
            <label>
                <?= __('Email sender', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_layout[sender]"
                       value="<?= esc_attr($email_layout['sender'] ?? get_bloginfo('admin_email')) ?>">
            </label>
        </p>
        <p>
            <label>
                <?= __('Email recipient', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_layout[recipient]"
                       value="<?= esc_attr($email_layout['recipient'] ?? get_bloginfo('admin_email')) ?>">
            </label>
        <p>
            <label>
                 <?= __('Subject of the email', 'influactive-forms') ?>
                <input type="text" name="influactive_form_email_layout[subject]"
                       value="<?= esc_attr($email_layout['subject'] ?? '') ?>">
            </label>
        </p>
        <p>
            <label>
                <?= __('Content of the email', 'influactive-forms') ?>
                <textarea name="influactive_form_email_layout[content]" cols="30"
                          rows="15"><?= esc_attr($email_layout['content'] ?? '') ?></textarea>

            </label>
        </p>
    </div>
    <?php

    // List all influactive_form_fields_name like "{field_name}"
    $fields = get_post_meta($post->ID, '_influactive_form_fields', true) ?? [];
    ?>
    <p><strong><?= __('Fields available in the email', 'influactive-forms') ?></strong></p>
    <ul>
        <?php foreach ($fields as $field): ?>
            <?php if ($field['type'] === 'select'): ?>
                <li>
                    <code>
                        {<?= strtolower($field['name']) ?>:label}
                    </code>
                </li>
                <li>
                    <code>
                        {<?= strtolower($field['name']) ?>:value}
                    </code>
                </li>
            <?php else: ?>
                <li><code>{<?= strtolower($field['name']) ?>}</code></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php
}

// Enregistrement des champs
add_action('save_post', 'influactive_form_save_post');
function influactive_form_save_post($post_id): void
{
    if (get_post_type($post_id) === 'influactive-forms') {
        $fields = $_POST['influactive_form_fields'] ?? [];
        $fields_type = $_POST['influactive_form_fields']['type'] ?? [];
        $fields_label = $_POST['influactive_form_fields']['label'] ?? [];
        $fields_name = $_POST['influactive_form_fields']['name'] ?? [];
        $fields_options = $_POST['influactive_form_fields']['options'] ?? [];
        $field_order = $_POST['influactive_form_fields']['order'] ?? [];

        for ($i = 0, $iMax = count($fields_name); $i < $iMax; $i++) {
            $options = [
                'label' => '',
                'value' => '',
            ];
            if (isset($fields_options[$field_order[$i]])) {
                foreach ($fields_options[$field_order[$i]] as $key => $option) {
                    $options[$key] = is_array($option)
                        ? array_map('sanitize_text_field', $option)
                        : sanitize_text_field($option);
                }
            }


            $fields[$i] = [
                'type' => sanitize_text_field($fields_type[$i]),
                'label' => sanitize_text_field($fields_label[$i]),
                'name' => strtolower(sanitize_text_field($fields_name[$i])),
                'order' => (int)$field_order[$i],
            ];

            if ($fields[$i]['type'] === 'select' && isset($fields_options[$field_order[$i]])) {
                $fields[$i]['options']['label'] = $options['label'];
                $fields[$i]['options']['value'] = $options['value'];
            }
        }

        update_post_meta($post_id, '_influactive_form_fields', $fields);

        $email_style = $_POST['influactive_form_email_style'] ?? [];
        $email_layout = $_POST['influactive_form_email_layout'] ?? [];

        // Sanitize email layout content
        if (isset($email_layout['content'])) {
            $email_layout['content'] = wp_kses_post($email_layout['content']);
        }
        // Sanitize email layout subject
        if (isset($email_layout['subject'])) {
            $email_layout['subject'] = sanitize_text_field($email_layout['subject']);
        }

        update_post_meta($post_id, '_influactive_form_email_style', $email_style);
        update_post_meta($post_id, '_influactive_form_email_layout', $email_layout);
    }
}
