<?php
/**
 * All functions necessary for volunteer registration are here
 *
 */

/**
 * Create a candidate and trigger an invite on Checkr.io
 * This is all done via a Caldera Form
 */

if (class_exists('Caldera_Forms_Autoloader') )
	add_action( 'sdrt_trigger_checkr_invite', 'sdrt_checkr_create_invite', 10, 2 );

function sdrt_checkr_create_invite( $data ) {

	// Arguments for POSTing the candidate info to Checkr
    $candidate_args = array(
        'method'            => 'POST',
        'headers'           => array(
            'Authorization' => 'Basic ' . base64_encode( SDRT_CHECKR_API  . ':' . '' )
        ),
        'body'              => array(
            'first_name'        => $data['first_name'],
            'no_middle_name'    => true,
            'last_name'         => $data['last_name'],
            'email'             => $data['email_address'],
            'dob'               => $data['your_date_of_birth'],
        ),
    );

    $candidate_response = wp_remote_request( 'https://api.checkr.com/v1/candidates',  $candidate_args );

    if ( is_wp_error( $candidate_response) ) {
        return false; // Bail early
    }

    // Get the Candidate response body from the Checkr response
    $candidate_body = wp_remote_retrieve_body( $candidate_response );

    // Decode the candidate response for better data consumption
    $candidate_data = json_decode( $candidate_body );

    if ( ! empty( $candidate_data ) ? $candidate_id = $candidate_data->id : $candidate_id = '' );

    // Arguments for POSTing the Invitation to Checkr based on the Candidate we just created.
    $invite_args = array(
        'method'            => 'POST',
        'headers'           => array(
            'Authorization' => 'Basic ' . base64_encode( SDRT_CHECKR_API  . ':' . '' )
        ),
        'body'              => array(
            'package'       => 'tasker_standard',
            'candidate_id'  => $candidate_id,
        ),
    );

    $invite_response = wp_remote_request( 'https://api.checkr.com/v1/invitations',  $invite_args );

    if ( is_wp_error( $invite_response) ) {
        return false; // Bail early
    }

    // Get the invitation response
    $invite_body = wp_remote_retrieve_body( $invite_response );

    // decode the invitation response
    $invite_data = json_decode( $invite_body );

    // get the invite url and candidate id to pass to the user we're creating with Caldera
    $invite_url = esc_url($invite_data->invitation_url);
	$candidate_id = strip_tags($invite_data->candidate_id);

	// Populate the hidden fields with the data
    Caldera_Forms::set_field_data( 'fld_468416', $invite_url, $data['__form_id'], $data['__entry_id'] );

	Caldera_Forms::set_field_data( 'fld_1569509', $candidate_id, $data['__form_id'], $data['__entry_id'] );
}

/**
 *  Create the Checkr Webhook
 *  Then conditionally update the user if they passed the backgroun check
 *  And send that user an email informing them they can now RSVP.
 */

add_action( 'init', 'sdrt_listen_for_checkr' );

function sdrt_listen_for_checkr() {

	// Listen for the webhook url: sdrefugeetutoring.com/?sdrt-listener=checkr
    if ( isset( $_GET['sdrt-listener'] ) && $_GET['sdrt-listener'] == 'checkr' ) {
        /**
         * Fires when Checkr webhook is sent
         *
         * @since 1.0
         */

        $webhook = file_get_contents( 'php://input' );
	    $webhook_data = json_decode( $webhook );

	    // Get the report type
        $report_type = $webhook_data->type;

        // Get the report status. Looking for "clear"
        $report_status = $webhook_data->data->object->status;
	    $candidate_id = $webhook_data->data->object->candidate_id;

	    // Find the existing WP user based on the "candidate id" that we created when they registered on the Caldera Form
        $user = get_users( array( 'meta_key'=>'background_check_candidate_id', 'meta_value'=>$candidate_id ) );

        // Get necessary user_meta values
        $user_id = $user[0]->ID;
        $user_role = $user[0]->roles;
        $user_email = $user[0]->data->user_email;

        // If the report is "clear" and the user associated with that report is a "Volunteer Pending", update the user and send them a confirmation email.
        if ( $report_type == 'report.completed' && $report_status == 'clear' && $user_role[0] == 'volunteer_pending' ) {

        	// The update user function. We're only updating their user role
	        wp_update_user( array( 'ID' => $user_id, 'role' => 'volunteer' ) );

	        // The email we send the user to confirm they cleared and can now RSVP.
	        $headers[] = 'From: SD Refugee Tutoring <info@sdrefugeetutoring.com>';

	        wp_mail( $user_email, 'You can now RSVP for Refugee Tutoring Sessions!', get_checkr_clear_email_body($user_id), $headers );
        }
		//var_dump($user);
	    wp_die();
    }
}

function get_checkr_clear_email_body($user_id) {
	ob_start();
	$usermeta = get_user_meta($user_id);
	$first = $usermeta['first_name'];
	?>
	<p>Dear <?php echo $first; ?>,</p>

	<p>Congratulations! We received an all clear on your background check. Thank you for taking that time and effort. Background checks ensure that our students are always in safe hands.</p>

	<p>We would love to have you begin volunteering as soon as possible. Please go to our Calendar to find the upcoming tutoring sessions that you are now eligble to RSVP for.</p>

	<p><strong>NOTE:</strong> You must login in order to RSVP for tutoring sessions. This ensures that only those who have passed background checks can tutor. Your password was emailed to you when you filled out your registration form. If you have lost that, feel free <a href="<?php echo wp_lostpassword_url( get_home_url('', '/events/') ); ?>" title="Lost Password">reset your password here</a>.</p>

	<p>Lastly, in order for our students to receive the dedicated attention they need and deserve, we ask all volunteers to RSVP for at least 3 tutoring sessions a month.</p>

	<p>Thank you again for your generosity in volunteering your valuable time for the benefit of our refugee students. We could not do this without you.</p>

	<p>Sincerely,<br />Your SD Refugee Tutoring Leadership Team</p>
	<?php
	$body = ob_get_clean();

	return $body;
}

