/**
 * Tethered's Connect JS class
 *
 * Version: 1.0.0
 * 
*/

class TetheredConnect{
	// static MYCONSTANT = '';

	/**
	 * Constructor 
	*/
	constructor(options){
		this.init();
    
    $ = jQuery;

		if(typeof $ === 'undefined'){
			console.error("jQuery dependency is not present, TetheredConnect could not be initialized");
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
			stage : 1,
			type : 'url',
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

		this.elements.configTypeList.find('input[value="url"]').click();
	}



	/**
	 * Find the relevant elements within the dom
	 * 
	 * @return void
	*/
	findElements(){
		this.elements = {};

    this.elements.connectButton = $('#tethered_connect_site');

		this.elements.typeRow = $('.tethered_connect_row[data-type="type"]');
		this.elements.connectionRow = $('.tethered_connect_row[data-type="connection"]');
		this.elements.advancedRow = $('.tethered_connect_row[data-type="advanced"]');
		this.elements.configTypeList = $('.tethered_config_type_list');
	}



	/**
	 * Bind all the events
	 * 
	 * @return void
	*/
	bindEvents(){

		this.elements.connectButton.on('click', (event) => {
      this.changeStage();
    })

		this.elements.configTypeList.find('input[name="config_type"]').on('click', (event) => {
			$('.tethered_config_type_list_item').removeClass('tethered_config_type_selected');

			let typeInput = $(event.currentTarget);
			typeInput.parent().addClass('tethered_config_type_selected');

			this.state.type = typeInput.val();

			$('input[name="config_type"]').removeAttr('checked');
			$('input[name="config_type"]').val([this.state.type]);

			this.updateInputs();
		})

	}

	
	
	updateInputs(){
		this.elements.connectionRow.find('.tethered_input_group').hide();
		this.elements.connectionRow.find('.tethered_input_group input').removeAttr('required');

		this.elements.connectionRow.find('.tethered_input_group[data-type="all"]').show();
		this.elements.connectionRow.find('.tethered_input_group[data-type="all"] input').attr('required', true);

		switch (this.state.type) {
			case 'port':
				this.elements.connectionRow.find('.tethered_input_group[data-type="port"]').show();
				this.elements.connectionRow.find('.tethered_input_group[data-type="port"] input').attr('required', true);
			break;

			case 'keyword':
				this.elements.connectionRow.find('.tethered_input_group[data-type="keyword"]').show();
				this.elements.connectionRow.find('.tethered_input_group[data-type="keyword"] input').attr('required', true);
			break;
		}
	}



  changeStage(stage = false){
		if(!stage){
			stage = this.state.stage + 1;
		}

    $('.tethered_get_started_stage').hide(250);
		$(`.tethered_get_started_stage[data-stage="${stage}"]`).show(150);

		this.state.stage = stage;
  }



}

let tetheredConnect = false;
jQuery(function(){
	/**
	 * Constructed in jQuery wrapper to allow the $ instance to be available in class
	 *
	 * The actual variable is defined globally, to expose it in the DOM
	*/
	tetheredConnect = new TetheredConnect();
});	