<?php

/**
 * Contact Inquiry Form — AJAX submission handler.
 *
 * The ai-zippy/contact-form block renders a nonce'd form (render.php prints
 * data-ajax-url / data-nonce on the wrapper); its view.js posts here with
 * action=sm_contact_submit. Flow: verify nonce → reject silently when the
 * honeypot field is filled → validate name/email/message → wp_mail() to the
 * site admin → JSON response (success, or per-field errors).
 */

defined('ABSPATH') || exit;

add_action('wp_ajax_sm_contact_submit', 'ai_zippy_child_contact_form_submit');
add_action('wp_ajax_nopriv_sm_contact_submit', 'ai_zippy_child_contact_form_submit');

/**
 * Handle a contact-form submission and reply with JSON.
 */
function ai_zippy_child_contact_form_submit(): void
{
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'sm_contact_submit')) {
        wp_send_json_error([
            'errors' => [
                'form' => __('Your session has expired. Please reload the page and try again.', 'ai-zippy-child'),
            ],
        ], 403);
    }

    // Honeypot: bots fill the hidden "company" field, people never see it.
    // Answer with plain success (no mail) so bots get no signal to retry.
    if (isset($_POST['company']) && '' !== trim((string) wp_unslash($_POST['company']))) {
        wp_send_json_success();
    }

    $name    = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email   = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';

    $errors = [];
    if ('' === $name) {
        $errors['name'] = __('Please enter your name.', 'ai-zippy-child');
    }
    if (!is_email($email)) {
        $errors['email'] = __('Please enter a valid email address.', 'ai-zippy-child');
    }
    if ('' === $message) {
        $errors['message'] = __('Please enter a message.', 'ai-zippy-child');
    }
    if ($errors) {
        wp_send_json_error(['errors' => $errors], 400);
    }

    $site    = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
    $to      = get_option('admin_email');
    $subject = sprintf(
        /* translators: 1: site name, 2: sender name. */
        __('[%1$s] Contact inquiry from %2$s', 'ai-zippy-child'),
        $site,
        $name
    );
    $body    = implode("\n", [
        sprintf(__('Name: %s', 'ai-zippy-child'), $name),
        sprintf(__('Email: %s', 'ai-zippy-child'), $email),
        '',
        $message,
        '',
        '—',
        home_url('/contact/'),
    ]);
    $headers = ['Reply-To: ' . $name . ' <' . $email . '>'];

    if (!wp_mail($to, $subject, $body, $headers)) {
        wp_send_json_error([
            'errors' => [
                'form' => __('Sorry, your message could not be sent. Please try again later.', 'ai-zippy-child'),
            ],
        ], 500);
    }

    wp_send_json_success();
}
