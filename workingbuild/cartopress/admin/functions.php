<?php
//generate table
		public function generate_table_process()
		{
		   check_ajax_referer('generate_table_nonce');
		   //
		   if (isset($_POST['action'])) {
				switch ($_POST['action']) {
					case 'generate_table':
						generate_table();
						break;
				}
			}
			else {
				echo "The insert function is called from else.";
			}
			function generate_table() {
				echo "The insert function is called.";
				exit;
			}
			//
		}
		generate_table_process();
?>