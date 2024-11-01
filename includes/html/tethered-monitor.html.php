<?php

if(!defined( 'ABSPATH' )){
	exit;
}

$this->load_part('head');

$user = wp_get_current_user();


?>

<div id="tethered_body">
  
  <?php $this->load_part("loader"); ?>

  <div class="tethered_monitor_container" data-id="<?php echo esc_attr($this->connected_site_details->id); ?>" style="display: none;">

    <div class="tethered_monitor_content">
      
      <div class="tethered_monitor_site_details">

        <div class="tethered_table tethered_monitor_table">
          <div class="tethered_table_head">
            <div class="tethered_table_row">
              <div class="tethered_table_col" data-type="connection">
                <span>Monitor</span>
              </div>
              <div class="tethered_table_col" data-type="type">
                <span>Type</span>
              </div>
              <div class="tethered_table_col" data-type="url">
                <span>URL</span>
              </div>
              <div class="tethered_table_col" data-type="actions"></div>
            </div>
          </div>
          <div class="tethered_table_body">
            <div class="tethered_table_row">
              <div class="tethered_table_col" data-type="connection">
                <span data-type="connection"></span>
              </div>
              <div class="tethered_table_col" data-type="type">
                <span data-type="type"></span>
              </div>
              <div class="tethered_table_col" data-type="url">
                <span data-type="url"></span>
              </div>
              <div class="tethered_table_col" data-type="actions">
                <span data-type="action_remove">Remove</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="tethered_monitor_block_container">

        <div class="tethered_monitor_block">
          <div class="tethered_monitor_block_head">
            <div class="tethered_monitor_block_row" data-type="connection">
              <span class="tethered_monitor_block_stat" data-stat="connection"></span>
              <span class="tethered_monitor_block_stat" data-stat="url"></span>
            </div>
            <div class="tethered_monitor_block_row" data-type="status">
              <span class="tethered_monitor_block_stat" data-stat="operational_state"><span class="tethered_monitor_block_stat" data-stat="operational_state_icon"></span><span class="tethered_monitor_block_stat" data-stat="operational_state_title"></span></span>
              <div class="tethered_monitor_block_row" data-type="uptime" style="display: none;">
                <span class="tethered_monitor_block_stat" data-stat="uptime"></span>
                <span class="tethered_monitor_block_stat" data-stat="uptime_percentage"></span>
              </div>
            </div>
          </div>
          <div class="tethered_monitor_block_chart_container">
            <div class="tethered_monitor_block_chart_inner">

              <div data-chart data-aggregator='uptime' data-boundary-top=220 class="tethered_monitor_block_chart" data-sets=''>
                <div class="tethered_monitor_block_chart_text">Historical data not available...</div>
              </div>

            </div>
          </div>
          <div class="tethered_monitor_block_body">
            <div class="tethered_monitor_block_row" data-type="average_time">
              <span class="tethered_monitor_block_stat_name" data-stat="average_time">Average time:</span>
              <span class="tethered_monitor_block_stat" data-stat="average_time"></span>
            </div>
            <div class="tethered_monitor_block_row" data-type="last_contact">
              <span class="tethered_monitor_block_stat_name" data-stat="last_contact">Last contact:</span>
              <span class="tethered_monitor_block_stat" data-stat="last_contact"></span>
            </div>
            <div class="tethered_monitor_block_row" data-type="connection_type">
              <span class="tethered_monitor_block_stat_name" data-stat="connection_type">Monitor type:</span>
              <span class="tethered_monitor_block_stat" data-stat="connection_type"></span>
            </div>
          </div>
          <div class="tethered_monitor_block_foot">
            <button id="tethered_monitor_block_view_full_report"><?php echo wp_kses(wp_remote_retrieve_body(wp_remote_get($this->image_dir_url . "icons/box-arrow-up-right.svg")), ['svg' => ['xmlns' => true, 'width' => true, 'height' => true, 'fill' => true, 'class' => true, 'viewbox'], 'path' => ['fill-rule' => true, 'd' => true]]); ?> View Full Report</button>
          </div>
        </div>

      </div>

    </div>

  </div>

</div>

<?php

$this->load_part('foot');

?>