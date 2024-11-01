/**
 * Tethered's Account JS class
 *
 * Version: 1.0.0
 * 
*/

class TetheredAccount{
	
	static STATUS_BAD_REQUEST_BOUNDARY = 400;

	/**
	 * Constructor 
	*/
	constructor(options){
		this.init();
    
    $ = jQuery;

		if(typeof $ === 'undefined'){
			console.error("jQuery dependency is not present, TetheredAccount could not be initialized");
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

		};
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
		
		this.requestProfileDetails();
	}



	/**
	 * Find the relevant elements within the dom
	 * 
	 * @return void
	*/
	findElements(){
		this.elements = {};

    this.elements.logout = $('#tethered_logout');

		this.elements.profilePage = $('.tethered_account_container');

		this.elements.loader = $('#tethered_loading_container');
		this.elements.loaderLogo = $('#tethered_loading_container #tethered_loading_logo');
		this.elements.loaderText = $('#tethered_loading_container #tethered_loading_text');

		this.elements.accountBlock = $('.tethered_account_block');
		this.elements.accountImage = $('#tethered_account_image');
		this.elements.accountName = $('#tethered_account_username');
		
		this.elements.siteType = $('#tethered_site_type');
		this.elements.siteUrl = $('#tethered_site_url');
		this.elements.siteConnected = $('#tethered_site_connected_timestamp');

		this.elements.siteLastLabel = $('#tethered_site_last_label');
		this.elements.siteLast = $('#tethered_site_last_timestamp');
		
		this.elements.viewMonitor = $('#tethered_site_view_monitor');

		this.elements.viewOnboardingDetails = $('#tethered_onboarding_view_details');
	}



	/**
	 * Bind all the events
	 * 
	 * @return void
	*/
	bindEvents(){

		this.elements.logout.on('click', (event) => {
		if(!confirm("Are you sure you want to logout?")){
			console.log("Cancelled");
			event.preventDefault();
		}
		})

		this.elements.viewMonitor.on('click', (event) => {
			window.location.href = tethered_account_helpers.site_monitor_link;
		})

		this.elements.viewOnboardingDetails.on('click', (event) => {
			$('.tethered_onboarding_detail_row').show();
			this.elements.viewOnboardingDetails.hide();
		})

	}



	/**
	 * Requests the profile details for a user
	 * 
	 * @returns void
	 */
	requestProfileDetails(){
		let url = tethered_account_helpers.rest_route_profile;
		$.ajax({
			type : "GET",
			dataType : "json",
			url : url,
			success : (response) => {
				if(response){
					this.raw_details = response;
					this.profile = JSON.parse(atob(response));

					
					this.profileDetails = {};
					this.profileDetails.avatar = this.profile.avatar;
					this.profileDetails.username = this.profile.username;
					
					console.log(this.profileDetails);

					if(this.state.id != 0 && this.state.id != false){
						this.requestReport();
					} else {
						this.populateProfileBlock();

						this.hideLoader();
						this.showProfilePage();
					}
				} else {
					this.failedRequest("Profile");
				}
			},
			error : (xhr, status, error) => {
				this.failedRequest("Profile", error);
			}
		})
	}



	/**
	 * Sets the site id in the class
	 * 
	 * @return void
	 */
	setSiteID(){
		this.state.id = tethered_account_helpers.site_id;
	}



	/**
	 * Requests the report for the site
	 * 
	 * @returns void
	 */
	requestReport(){
		if(this.state.id){
			let url = tethered_account_helpers.rest_route_report + this.state.id;
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
						this.reportDetails.raw_url = this.report.labels.populate.path;
						this.reportDetails.url = "https://" + this.reportDetails.raw_url;
						
						this.reportDetails.last_label = this.report.labels.populate.label_contact_latest;
						this.reportDetails.last = this.report.labels.populate.last_time;
						
						this.reportDetails.type = this.report.labels.populate.connection_type;
						
	
						console.log(this.report);
						console.log(this.reportDetails);
	
						this.populateProfileBlock();

						this.hideLoader();
						this.showProfilePage();
					} else {
						this.failedRequest('Report');
					}
				},
				error : (xhr, status, error) => {
					this.failedRequest('Report', error);
				}
			})
		}	else {
			console.log("No site ID");
			return false;
		}
	}



	/**
	 * Populates the profile block
	 * 
	 * @return void
	 */
	populateProfileBlock(){

		if(typeof this.profileDetails.avatar != 'undefined' && this.profileDetails.avatar != null && (this.profileDetails.avatar).trim() != ''){
			this.elements.accountImage.attr('src', this.profileDetails.avatar);
		}

		this.elements.accountName.html(this.profileDetails.username);

		if(typeof this.reportDetails != 'undefined'){
			this.elements.siteUrl.html(this.reportDetails.raw_url);
			this.elements.siteType.html(this.reportDetails.type);
			this.elements.siteLastLabel.html(this.reportDetails.last_label + ": ");
			this.elements.siteLast.html(this.reportDetails.last);
		} else {
			$(`<div class="tethered_account_site_not_connected_message"><p>This site is not being monitored. Connect it to start monitoring.</p><a href="${tethered_account_helpers.site_monitor_link}" class="tethered_button tethered_primary_button">Connect Site</a></div>`).insertAfter('.tethered_account_details');
		}
		
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
	 * Shows the account page container
	 * 
	 * @return void
	 */
	showProfilePage(){
		this.elements.profilePage.show(250);
	}



	/**
	 * Hides the account page container
	 * 
	 * @return void
	 */
	hideProfilePage(){
		this.elements.profilePage.hide(250);
	}


	/**
	 * Displays error message and removes loading animation
	 * 
	 * @param string type
	 * @param string error
	 * 
	 * @return void
	 */
	failedRequest(type, error = false){
		if(error == false){
			error = "Something went wrong fetching your " + type + " details... Please try again.";
		}
		this.elements.loaderText.html(error);
		this.elements.loaderLogo.removeClass('tethered_blinking');
		this.elements.profilePage.remove();
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


}

let tetheredAccount = false;
jQuery(function(){
	/**
	 * Constructed in jQuery wrapper to allow the $ instance to be available in class
	 *
	 * The actual variable is defined globally, to expose it in the DOM
	*/
	tetheredAccount = new TetheredAccount();
});	