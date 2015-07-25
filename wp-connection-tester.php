<?php

/*
	Plugin Name: Fatcat Apps Connection Tester
	Plugin URI: https://fatcatapps.com/
	Description: Check if a WordPress website is able to connect to the Fatcat Apps licensing server.
	Version: 1.0.0
	Author: Fatcat Apps
	Author URI: https://fatcatapps.com/
	License: GPL v3

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class FCA_Connection_Tester {

	private $urls = array(
		'fca' => 'https://fatcatapps.com/'
	);

	private $admin_message = array();

	/**
	 * Setup plugin
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ) );
		add_action( 'admin_init', array( $this, 'catch_post' ) );
		add_action( 'admin_notices', array( $this, 'admin_messages' ) );
	}

	/**
	 * Add submenu page
	 */
	public function add_submenu_page() {
		add_submenu_page( 'options-general.php', 'Fatcat Apps Connection Tester', 'Fatcat Apps Connection Tester', 'manage_options', 'fca_connection_tester', array(
			$this,
			'screen'
		) );
	}

	/**
	 * Output the screen
	 */
	public function screen() {
		?>
		<div class="wrap" xmlns="http://www.w3.org/1999/html">

			<div>
				<h2>Connection Tester</h2>

				<?php
				if ( function_exists( 'curl_version' ) ) {
					$curl_version = curl_version();
					$curl_status  = '<span style="font-weight:bold;color:#ff0000;">INCOMPATIBLE</span>';
					if ( version_compare( $curl_version['version'], '7.18.1', '>=' ) ) {
						$curl_status = '<span style="font-weight:bold;color:#00ff00;">COMPATIBLE</span>';
					}
					?>
					<p><strong>Curl:</strong> <?php echo $curl_version['version'] . ' ' . $curl_status; ?></p>
				<?php
				} else {
					echo 'curl_version() doesn\'t exist';
				}
				?>

				<form method="post"
				      action="<?php echo admin_url( 'options-general.php?page=fca_connection_tester' ); ?>">
					<input type="hidden" name="bk_site" value="fca" id="fca" placeholder=""/>

					<p><input type="submit" name="bk_active" value="Test"
					          class="button button-primary"/></p>
				</form>
			</div>
		</div>
	<?php
	}

	/**
	 * Catch activation call
	 */
	public
	function catch_post() {

		if ( isset( $_POST['bk_site'] ) && isset( $this->urls[ $_POST['bk_site'] ] ) ) {

			$url = $this->urls[ $_POST['bk_site'] ];

			$result = wp_remote_get( $url );

			if ( ! is_wp_error( $result ) ) {
				$this->admin_message = array(
					'message' => sprintf( 'Connection to %s successful!', $url ),
					'type'    => 'updated'
				);
			} else {
				$this->admin_message = array(
					'message' => sprintf( 'Connection to %s failed! <br/> %s', $url, print_r( $result, 1 ) ),
					'type'    => 'updated'
				);
			}


		}

	}

	/**
	 * Display message
	 */
	public
	function admin_messages() {
		if ( count( $this->admin_message ) > 0 ) {
			?>
			<div class="<?php echo $this->admin_message['type']; ?>">
				<p><?php echo $this->admin_message['message']; ?></p>
			</div>
		<?php
		}
	}

}

function __fca_connection_tester() {
	new FCA_Connection_Tester();
}

add_action( 'plugins_loaded', '__fca_connection_tester', 11 );
