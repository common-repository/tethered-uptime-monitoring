/**
 * Tethered's Monitor JS class
 *
 * Version: 1.0.0
 * 
*/

class TetheredMonitor{

	static STATUS_BAD_REQUEST_BOUNDARY = 400;

	/**
	 * Constructor 
	*/
	constructor(options){
		this.init();
    
    $ = jQuery;

		if(typeof $ === 'undefined'){
			console.error("jQuery dependency is not present, TetheredMonitor could not be initialized");
			return;
		}

		$(document).ready(
      this.onReady()
    );
	}



	/**
	 * Initialize defaults, state trackers and instance variables
	 * 
	 * @return void
	*/
	init(){
		this.state = {
			id : false
		};
		
		
		this.raw_report = false;
		this.report = false;
		this.reportDetails = false;
	}


	/**
	 * OnReady delegate, completes the initialization
	 * 
	 * @return void
	*/
	onReady(){       
		this.findElements();
		this.bindEvents();

		this.setSiteID();

		this.requestReport();
	}



	/**
	 * Find the relevant elements within the dom
	 * 
	 * @return void
	*/
	findElements(){
		this.elements = {};

		this.elements.monitor = $('.tethered_monitor_container');
		
		this.elements.loader = $('#tethered_loading_container');
		this.elements.loaderLogo = $('#tethered_loading_container #tethered_loading_logo');
		this.elements.loaderText = $('#tethered_loading_container #tethered_loading_text');

		this.elements.tableBody = {};
		this.elements.tableBody.connection = $('.tethered_monitor_table .tethered_table_body .tethered_table_col[data-type="connection"]');
		this.elements.tableBody.type = $('.tethered_monitor_table .tethered_table_body .tethered_table_col[data-type="type"]');
		this.elements.tableBody.url = $('.tethered_monitor_table .tethered_table_body .tethered_table_col[data-type="url"]');

		this.elements.monitorBlock = $('.tethered_monitor_block')

		this.elements.monitorBlockHead = {};
		this.elements.monitorBlockHead.connection = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="connection"]');
		this.elements.monitorBlockHead.url = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="url"]');
		this.elements.monitorBlockHead.operational_state = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="operational_state"]');
		this.elements.monitorBlockHead.operational_state_icon = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="operational_state_icon"]');
		this.elements.monitorBlockHead.operational_state_title = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="operational_state_title"]');

		this.elements.monitorBlockHead.uptime = $('.tethered_monitor_block_row[data-type="uptime"]');
		this.elements.monitorBlockHead.uptime_percentage_label = $('.tethered_monitor_block_stat[data-stat="uptime"]')
		this.elements.monitorBlockHead.uptime_percentage = $('.tethered_monitor_block_head .tethered_monitor_block_stat[data-stat="uptime_percentage"]');

		this.elements.monitorBlockBody = {};
		this.elements.monitorBlockBody.average_time_label = $('.tethered_monitor_block_stat_name[data-stat="average_time"]')
		this.elements.monitorBlockBody.average_time = $('.tethered_monitor_block_body .tethered_monitor_block_stat[data-stat="average_time"]');

		this.elements.monitorBlockBody.last_contact_label = $('.tethered_monitor_block_stat_name[data-stat="last_contact"]')
		this.elements.monitorBlockBody.last_contact = $('.tethered_monitor_block_body .tethered_monitor_block_stat[data-stat="last_contact"]');
		
		this.elements.monitorBlockBody.connection_type = $('.tethered_monitor_block_body .tethered_monitor_block_stat[data-stat="connection_type"]');

		this.elements.monitorBlockChartContainer = $('.tethered_monitor_block_chart_container');
		this.elements.monitorBlockChart = $('.tethered_monitor_block_chart');

		this.elements.monitorBlockViewFullReport = $('#tethered_monitor_block_view_full_report');
		
	}



	/**
	 * Bind all the events
	 * 
	 * @return void
	*/
	bindEvents(){

		$(document).on('click', '#tethered_remove_site, span[data-type="action_remove"]', (event) => {
			let button = $(event.currentTarget);
			if(confirm("Are you are sure you want to remove this monitor?")){
				button.html("Removing");
				this.removeSite();
			}
		})

		this.elements.monitorBlockViewFullReport.on('click', (event) => {
			window.open(tethered_monitor_helpers.app_full_report_link, '_BLANK');
		})

	}



	/**
	 * Sets the site id in the class
	 * 
	 * @return void
	 */
	setSiteID(){
		this.state.id = tethered_monitor_helpers.site_id;
	}



	/**
	 * Requests the report for the site
	 * 
	 * @returns void
	 */
	requestReport(){
		if(this.state.id){
			let url = tethered_monitor_helpers.rest_route_report + this.state.id;
			$.ajax({
				type : "GET",
				dataType : "json",
				url : url,
				success : (response) => {
					if(response){
						this.raw_report = response;
						this.report = JSON.parse(atob(response));
						
						this.reportDetails = {};
						this.reportDetails.connection = this.report.labels.populate.title;
						this.reportDetails.type = this.report.labels.populate.connection_type;
						this.reportDetails.raw_url = this.report.labels.populate.path;
						this.reportDetails.url = "https://" + this.reportDetails.raw_url;
						
						this.reportDetails.status_code = this.report.overview.latest.status;
						this.reportDetails.operational_class = '';
						if(this.report.overview.latest == false){
							this.reportDetails.operational_state = this.report.labels.populate.label_status_pending_title;
							this.reportDetails.operational_icon = tethered_report_helpers.operational_pending_svg_code;
							this.reportDetails.operational_class = "tethered_site_queued";
						} else {
							if(this.isOperational(this.reportDetails.status_code)){
								this.reportDetails.operational_icon = tethered_report_helpers.operational_success_svg_code;
								this.reportDetails.operational_state = this.report.labels.populate.label_status_success_title;
								this.reportDetails.operational_class = "tethered_site_success";
							} else {
								this.reportDetails.operational_icon = tethered_report_helpers.operational_failure_svg_code;
								this.reportDetails.operational_state = this.report.labels.populate.label_status_fail_title;
								this.reportDetails.operational_class = "tethered_site_fail";
							}
						}
	
						this.reportDetails.uptime_percentage_label = this.report.labels.populate.label_metrics_status_title;
						this.reportDetails.uptime_percentage = this.report.labels.populate.uptime_percentage;

						this.reportDetails.average_time_label = this.report.labels.populate.label_metrics_value_average;
						this.reportDetails.average_time = this.report.labels.populate.avg_response_time;

						this.reportDetails.last_contact_label = this.report.labels.populate.label_contact_latest;
						this.reportDetails.last_contact = this.report.labels.populate.last_time;

						this.reportDetails.chart = this.report.chart;
	
						this.populateMonitorBlock();
	
						this.hideLoader();
						this.showMonitor();

						this.processChart();
					} else {
						this.failedReportRequest();
					}
				},
				error : (xhr, status, error) => {
					this.failedReportRequest(error);
				}
			})
		}	else {
			console.log("No site ID");
			return false;
		}
	}



	/**
	 * Populates the monitor block
	 * 
	 * @returns void
	 */
	populateMonitorBlock(){
		if(this.reportDetails != false){
			this.elements.tableBody.connection.html(this.reportDetails.connection);
			this.elements.tableBody.type.html(this.reportDetails.type);
			this.elements.tableBody.url.html(this.reportDetails.url);
			this.elements.monitorBlockHead.connection.html(this.reportDetails.connection);
			this.elements.monitorBlockHead.url.html(this.reportDetails.raw_url);
			this.elements.monitorBlockHead.operational_state.addClass(this.reportDetails.operational_class)
			this.elements.monitorBlockHead.operational_state_icon.html(this.reportDetails.operational_icon);
			this.elements.monitorBlockHead.operational_state_title.html(this.reportDetails.operational_state);

			const uptimeDisplayTypes = ['url', "SSL Certificate"];
			if(uptimeDisplayTypes.includes(this.reportDetails.type)){
				this.elements.monitorBlockHead.uptime.show();
				this.elements.monitorBlockHead.uptime_percentage_label.html(this.reportDetails.uptime_percentage_label)
				this.elements.monitorBlockHead.uptime_percentage.html(this.reportDetails.uptime_percentage);
			}

			this.elements.monitorBlockBody.average_time_label.html(this.reportDetails.average_time_label);
			this.elements.monitorBlockBody.average_time.html(this.reportDetails.average_time);
			
			this.elements.monitorBlockBody.last_contact_label.html(this.reportDetails.last_contact_label);
			this.elements.monitorBlockBody.last_contact.html(this.reportDetails.last_contact);
			
			this.elements.monitorBlockBody.connection_type.html(this.reportDetails.type);


			this.elements.monitorBlockChart.attr('data-sets', JSON.stringify([this.array_values_equivalent(this.reportDetails.chart.data)]));

			// console.log(JSON.stringify([this.array_values_equivalent(this.reportDetails.chart.data)]));
		} else {
			this.failedReportRequest();
		}
	}



	/**
	 * Process the charts
	 * 
	 * @return void
	 */
	processChart(){		
		if(this.reportDetails.chart.data != false){
			this.elements.monitorBlockChart.empty();
			
			let chartHeight = this.elements.monitorBlockChartContainer.height();
			let chartWidth = this.elements.monitorBlockChartContainer.width();
	
			let tetheredCharts = new TetheredCharts(chartHeight, chartWidth);
		}
	}



	array_values_equivalent(obj){
		let aux = [];
		for (let i in obj) {
			if (obj.hasOwnProperty(i)) {
				aux.push(obj[i]);
			}
		}
	
		return aux;
	}



	/**
	 * Removes the loader
	 * 
	 * @return void
	 */
	hideLoader(){
		this.elements.loader.hide(250);
	}



	/**
	 * Shows the loader
	 * 
	 * @return void
	 */
	showLoader(){
		this.elements.loader.show(250);
	}



	/**
	 * Shows the monitor container
	 * 
	 * @return void
	 */
	showMonitor(){
		this.elements.monitor.show(250);
	}



	/**
	 * Hides the monitor container
	 * 
	 * @return void
	 */
	hideMonitor(){
		this.elements.monitor.hide(250);
	}



	/**
	 * Displays error message and removes loading animation
	 * 
	 * @param string error
	 * 
	 * @return void
	 */
	failedReportRequest(error = false){
		if(error == false){
			error = "Something went wrong fetching your site report... Please try again.";
		}
		this.elements.loaderText.html(error);
		this.elements.loaderLogo.removeClass('tethered_blinking');
		this.elements.monitor.remove();

		$(`<p class="tethered_failed_remove_option">If you are still experiencing issues, please may you remove your site and reconnect it.<button class="tethered_button tethered_caution_button" id="tethered_remove_site">Remove Site</button></p>`).insertAfter(this.elements.loaderText);

	}



	/**
	 * Returns if code is considered operational
	 * 
	 * @param int code
	 * 
	 * @return boolean
	 */
	isOperational(code){
		if(typeof code !== 'undefined' && code != null && parseInt(code) < tethered_report_helpers.status_bad_request_boundary){
			return true;
		} else {
			return false;
		}
	}



	/**
	 * Removes a site
	 * 
	 * @return void
	 */
	removeSite(){
		let url = tethered_monitor_helpers.rest_route_remove + this.state.id;
		$.ajax({
			type : "GET",
			dataType : "json",
			url : url,
			success : (response) => {

				window.location.reload();

			},
			error : (xhr, status, error) => {
				
				window.location.reload();

			}
		})
	}



}

let tetheredMonitor = false;
jQuery(function(){
	/**
	 * Constructed in jQuery wrapper to allow the $ instance to be available in class
	 *
	 * The actual variable is defined globally, to expose it in the DOM
	*/
	tetheredMonitor = new TetheredMonitor();
});	