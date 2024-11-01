/**
 * Tethered's Account Login JS class
 *
 * Version: 1.0.0
 * 
*/

class TetheredAccountLogin{
	// static MYCONSTANT = '';

	/**
	 * Constructor 
	*/
	constructor(options){
		this.init();
    
    $ = jQuery;

		if(typeof $ === 'undefined'){
			console.error("jQuery dependency is not present, TetheredAccountLogin could not be initialized");
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
      action : 'sign_in'
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
	}



	/**
	 * Find the relevant elements within the dom
	 * 
	 * @return void
	*/
	findElements(){
		this.elements = {};
    
    this.elements.inputs = {};
    this.elements.inputs.email = $('#tethered_email');
    this.elements.inputs.password = $('#tethered_password');
    this.elements.inputs.action = $('#tethered_action');
    
    this.elements.submit = $('#tethered_account_submit');

    this.elements.formLinksContainer = $('.tethered_form_links');
    
    this.elements.signUpLinks = $('#tethered_sign_up_links');
    this.elements.signUpLink = $('#tethered_signup_link');

    this.elements.signInLinks = $('#tethered_sign_in_links');
    this.elements.signInLink = $('#tethered_signin_link');

    this.elements.formTitleAction = $('#tethered_form_title_action');

    this.elements.form = $('#tethered_account_form');
	}



	/**
	 * Bind all the events
	 * 
	 * @return void
	*/
	bindEvents(){

    this.elements.signUpLink.on('click', (event) => {
      this.state.action = 'sign_up';
      this.updateForm();
    })

    this.elements.signInLink.on('click', (event) => {
      this.state.action = 'sign_in';
      this.updateForm();
    })

	}

  updateForm(){
    this.elements.inputs.action.val(this.state.action);

    this.elements.formLinksContainer.find('> p').hide();
    
    if(this.state.action == 'sign_up'){
      this.elements.signInLinks.show();
      this.elements.formTitleAction.html("up");
      this.elements.submit.val("Sign Up");

      this.elements.form.find('.tethered_input_group[data-type="name"]').show();
      this.elements.form.find('.tethered_input_group[data-type="name"] input').attr('required', true);
    } else if(this.state.action == 'sign_in') {
      this.elements.signUpLinks.show();
      this.elements.formTitleAction.html("in");
      this.elements.submit.val("Sign In");

      this.elements.form.find('.tethered_input_group[data-type="name"]').hide();
      this.elements.form.find('.tethered_input_group[data-type="name"] input').removeAttr('required');
    }
  }
}

let tetheredAccountLogin = false;
jQuery(function(){
	/**
	 * Constructed in jQuery wrapper to allow the $ instance to be available in class
	 *
	 * The actual variable is defined globally, to expose it in the DOM
	*/
	tetheredAccountLogin = new TetheredAccountLogin();
});	