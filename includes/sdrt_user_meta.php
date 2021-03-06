<?php
/**
 * Adds custom User Meta to User Profile
 * But only Admin and SDRT Leadership can edit it
 */

add_action( 'show_user_profile', 'sdrt_user_meta_fields' );
add_action( 'edit_user_profile', 'sdrt_user_meta_fields' );

function sdrt_user_meta_fields( $user ) { ?>
	<?php

		$userid = (is_admin()) ? $user->ID : get_current_user_id();

		$background_check_status = get_user_meta( $userid, 'background_check', false );
		$orientation = get_user_meta( $userid, 'sdrt_orientation_attended', false );
		$coc = get_user_meta( $userid, 'sdrt_coc_consented', false );
		$waiver = get_user_meta( $userid, 'sdrt_waiver_consented', false );

		$background_check_invite_url = get_user_meta( $userid, 'background_check_invite_url', false );
		$background_check_candidate_id = get_user_meta( $userid, 'background_check_candidate_id', true );

		switch ( $background_check_status[0] ) {
			case 'Yes':
				$background_check['icon'] = '<i class="fa fa-check-circle"></i>';
				$background_check['status'] = 'status-cleared';
				$background_check['message'] = 'You have <span>CLEARED</span> your background check. Thank you!';
				break;
			
			case 'No':
				$background_check['icon'] = '<i class="fa fa-times-circle"></i>';
				$background_check['status'] = 'status-failed';
				$background_check['message'] = 'Your background check did not clear. You are not allowed to tutor with us at this time. If you have questions about this at all, please contact boardmembers@sdrefugeetutoring.com';
				break;

			case 'Invited':
				$background_check['icon'] = '<i class="fa fa-question-circle"></i>';
				$background_check['status'] = 'status-pending';
				$background_check['message'] = 'You have been invited to take a background check and your status is still pending. You can check on the status at <a href="' . $background_check_invite_url[0] . '" target="_blank" rel="noopener noreferrer">the Checkr website</a>.';
				break;
		}

		switch ( $orientation[0] ) {
			case 'Yes':
				$orientation_status['icon'] = '<i class="fa fa-check-circle"></i>';
				$orientation_status['status'] = 'status-cleared';
				$orientation_status['message'] = 'You have <span>attended</span> an orientation session. Thank you!';
				break;
			
			default :
				$orientation_status['icon'] = '<i class="fa fa-times-circle"></i>';
				$orientation_status['status'] = 'status-failed';
				$orientation_status['message'] = 'You have <span>not yet attended</span> an orientation session. Please see <a href="' . get_home_url(). '/events" target="_blank" rel="noopener noreferrer">our calendar</a> to RSVP for an upcoming session.';
				break;
		}

		switch ( $coc[0] ) {
			case 'Yes':
				$coc_status['icon'] = '<i class="fa fa-check-circle"></i>';
				$coc_status['status'] = 'status-cleared';
				$coc_status['message'] = 'You have <span>indicated your consent</span> to our Code of Conduct. Thank you!';
				break;
			
			default:
				$coc_status['icon'] = '<i class="fa fa-times-circle"></i>';
				$coc_status['status'] = 'status-failed';
				$coc_status['message'] = 'You have <span>not yet indicated your consent</span> to our Code of Conduct. <a href="' . get_home_url() . '/code-of-conduct">Please do that here</a>.';
				break;
		}

		switch ( $waiver[0] ) {
			case 'Yes':
				$waiver_status['icon'] = '<i class="fa fa-check-circle"></i>';
				$waiver_status['status'] = 'status-cleared';
				$waiver_status['message'] = 'You have <span>indicated your consent</span> to our Volunteer Release Policy. Thank you!';
				break;
			
			default:
				$waiver_status['icon'] = '<i class="fa fa-times-circle"></i>';
				$waiver_status['status'] = 'status-failed';
				$waiver_status['message'] = 'You have <span>not yet indicated your consent</span> to our Volunteer Release & Waiver of Liability Policy. <a href="' . get_home_url() . '/volunteer-release">Please do that here</a>.';
				break;
		}
	?>

	<?php
	
	if ( !current_user_can( 'can_view_rsvps' ) ) : 
	/* TESTING PURPOSES
	echo "user_id =" . $userid . "<br />";
	echo "background_check_status = " .  $background_check_status[0] . "<br />";
	var_dump($background_check);

	echo "orientation = " .  $orientation[0];
	var_dump($orientation_status);

	echo "coc_status = " .  $coc[0];
	var_dump($coc_status);

	echo "media_status = " .  $media[0];
	var_dump($media_status);

	
	*/
	//var_dump(get_user_meta($userid));
	?>
	<div class="vol-reqs">
		<!-- Volunteer View of the Admin Profile Editor -->
		
		<!-- BACKGROUND CHECK STATUS -->
		<h2>YOUR SDRT VOLUNTEER REQUIREMENTS STATUS:</h2>
		<div id="background-check" class="req <?php echo $background_check['status']; ?>">
			<h3><?php echo $background_check['icon']; ?> Background Check</h3>
			<p class="description"><strong>REQUIREMENT:</strong><br />To volunteer, you must apply for and clear a background check via Checkr -- our online background check partner.</p>

			<p class="status-message"><strong>STATUS:</strong><br />
				<?php echo $background_check['message']; ?>
			</p>
		</div>

		<!-- ORIENTATION STATUS -->
		<div id="orientation-status" class="req <?php echo $orientation_status['status']; ?>">
			<h3><?php echo $orientation_status['icon']; ?> ORIENTATION STATUS</h3>
			<p class="description"><strong>REQUIREMENT:</strong><br />Attendance of required yearly Refresher or Orientation session(s). The specific requirements will be posted on the Volunteer registration page. And will always be relayed thru other avenues.</p>

			<p class="status-message"><strong>STATUS:</strong><br />
			<?php echo $orientation_status['message']; ?>
			</p>
		</div>

		<!-- COC STATUS -->
		<div id="coc-status" class="req <?php echo $coc_status['status']; ?>">
			<h3><?php echo $coc_status['icon']; ?> CODE OF CONDUCT STATUS</h3>
			<p class="description"><strong>REQUIREMENT:</strong><br />To volunteer, you must agree to our Code of Conduct.</p>

			<p class="status-message"><strong>STATUS:</strong><br />
				<?php echo $coc_status['message']; ?>
			</p>
		</div>

		<!-- MEDIA RELEASE STATUS -->
		<div id="mr-status" class="req <?php echo $waiver_status['status']; ?>">
			<h3><?php echo $waiver_status['icon']; ?> VOLUNTEER RELEASE STATUS</h3>
			<p class="description"><strong>REQUIREMENT:</strong><br />To volunteer, you must agree to our Volunteer Release & Waiver of Liability.</p>

			<p class="status-message"><strong>STATUS:</strong><br />
				<?php echo $waiver_status['message']; ?>
			</p>
		</div>
	</div><!-- /.vol-reqs -->
	<?php endif; ?>
	
	<?php if ( is_admin() && current_user_can( 'can_view_rsvps' )  ) : ?>

	<div class="vol-reqs">
	<?php 
		//var_dump(get_user_meta($userid)); 
		//var_dump($orientation[0]);
		?>
		<!-- SDRT Leadership View of Profile Edit Screen -->
		<!-- BACKGROUND CHECK STATUS -->
		<h2>Volunteer Requirement Status for:<br />
		<span><?php echo $user->first_name . '&nbsp;' . $user->last_name; ?></span></h2>
		
		<!-- BACKGROUND CHECK STATUS -->
		<div id="background-check" class="req <?php echo $background_check['status']; ?>">
			<h3>Background Check Status</h3>
			<p class="description">This information is automatically updated when they volunteer registers on the site, and then after their background check is completed. You can change the status manually if you need to, but not the link or candidate ID.</p>
			<p class="status">
				<select name="background_check" id="background_check">
					<option value="No" <?php  selected( $background_check_status[0], 'No' ); ?> >NO -- this volunteer has not yet registered.</option>

					<option value="Invited" <?php  selected( $background_check_status[0], 'Invited' ); ?> >An Invite has been sent, but we do not have results yet.</option>

					<option value="Yes" <?php  selected( $background_check_status[0], 'Yes' ); ?> >YES -- there is a passed background check on file.</option>
				</select>
			</p>
			<p class="checkr-info">
				<strong>This volunteer's Invite link is:</strong><br />
				<input type="text" name="background_check_invite_url" value="<?php echo $background_check_invite_url[0]; ?>" readonly size="75">
			</p>
			<p><strong>This volunteer's Candidate ID is:</strong><br />
				<input type="text" name="background_check_candidate_id" value="<?php echo $background_check_candidate_id; ?>" readonly size="75">
			</p>
		</div>

		<!-- ORIENTATION STATUS -->
		<div id="orientation-status" class="req <?php echo $orientation_status['status']; ?>">
			<h3>Orientation Status</h3>
			<p class="description">This setting must be changed manually by anyone with an SDRT Leadership Role. It is not ever updated automatically.</p>
			<p class="status">
				<select name="sdrt_orientation_attended" id="sdrt_orientation_attended">
					<option value="No" <?php  selected( $orientation[0], 'No' ); ?> >NO -- this volunteer has not yet attended an orientation.</option>

					<option value="Yes" <?php  selected( $orientation[0], 'Yes' ); ?> >YES -- this volunteer attended an orientation.</option>
				</select>
			</p>
		</div>

		<!-- COC STATUS -->
		<div id="coc-status" class="req <?php echo $coc_status['status']; ?>">
			<h3>Code of Conduct Status</h3>
			<p class="description">This is set to "No" by default for all users. It will be updated automatically once the user has completed the Code of Conduct Form.</p>
			<p class="status">
			<select name="sdrt_coc_consented" id="sdrt_coc_consented">
				<option value="No" <?php  selected( $coc[0], 'No' ); ?> >NO -- this volunteer has not consented to the SDRT Code of Conduct.</option>

				<option value="Yes" <?php  selected( $coc[0], 'Yes' ); ?> >YES -- this volunteer has consented to the SDRT Code of Conduct.</option>
			</select>
			</p>
		</div>

		<!-- VOLUNTEER RELEASE STATUS -->
		<div id="waiver-status" class="req <?php echo $waiver_status['status']; ?>">
			<h3>Volunteer Release Status</h3>
			<p class="description">This is automatically set to "No" by default for all users. It will be updated automatically once the user has completed the Volunteer Release & Waiver Form.</p>
			<p class="status">
				<select name="sdrt_waiver_consented" id="sdrt_waiver_consented">
					<option value="No" <?php  selected( $waiver[0], 'No' ); ?> >NO -- this volunteer has not consented to the SDRT Volunteer Release & Waiver of Liability.</option>

					<option value="Yes" <?php  selected( $waiver[0], 'Yes' ); ?> >YES -- this volunteer has consented to the Volunteer Release & Waiver of Liability.</option>
				</select>
			</p>
		</div>
	</div><!-- /.vol-reqs -->
	<?php endif;
}

add_action( 'personal_options_update', 'sdrt_save_user_meta_fields' );
add_action( 'edit_user_profile_update', 'sdrt_save_user_meta_fields' );

function sdrt_save_user_meta_fields( $user_id ) {

	if ( !current_user_can( 'can_view_rsvps', $user_id ) )
		return false;

	/*
	 * NOTE FOR FUTURE MATT:
	 * One way we can bulk "reset" the requirements for all Volunteers is that we'll add a new user meta every year. So right now it's "sdrt_orientation_attended" but next year it can be "sdrt_orientation_attended_2021" 
	 */
	
	/* Copy and paste this line for additional fields. */
	update_user_meta( $user_id, 'background_check', $_POST['background_check'] );

	update_user_meta( $user_id, 'background_check_candidate_id', $_POST['background_check_candidate_id'] );

	update_user_meta( $user_id, 'sdrt_orientation_attended', $_POST['sdrt_orientation_attended'] );

	update_user_meta( $user_id, 'sdrt_coc_consented', $_POST['sdrt_coc_consented'] );

	update_user_meta( $user_id, 'sdrt_waiver_consented', $_POST['sdrt_waiver_consented'] );
}