jQuery(
	function($) {
		'use strict';

		const payever_wc_claim_upload = {
			init: function () {
				this.cacheElements();
				this.bindEvents();
			},

			cacheElements: function () {
				this.$payeverClaimUpload       = $( 'div.wc-payever-claim-upload' );
				this.$payeverClaimUploadButton = $( '#payever-claim-upload-action' );
			},

			bindEvents: function () {
				$( '#woocommerce-order-items' )
					.on( 'click', 'button.wc-payever-claim-upload-button', this.claimUploadShow.bind( this ) )
					.on( 'change', '#claim_upload_files', this.handleUploadChange.bind( this ) )
					.on( 'click', '#payever-claim-upload-action', this.doClaimUpload.bind( this ) );

				this.$payeverClaimUpload.appendTo( '#woocommerce-order-items .inside' );
			},

			handleUploadChange: function () {
				this.$payeverClaimUploadButton.prop( 'disabled', ! $( '#claim_upload_files' ).val() );
			},

			claimUploadShow: function () {
				this.$payeverClaimUpload.slideDown();
				$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-payever-claim-upload' ).slideUp();
				$( 'div.wc-order-totals-items' ).slideUp();
			},

			doClaimUpload: function () {
				const formData = new FormData();
				formData.append( 'action', 'payever_claim_upload' );
				formData.append( 'order_id', woocommerce_admin_meta_boxes.post_id );
				formData.append( 'nonce', woocommerce_admin_meta_boxes.payever_claim_upload_nonce );

				$.each(
					$( '#claim_upload_files' ),
					function(i, file) {
						$.each(
							file.files,
							function(key, file){
								formData.append( `claim_upload_files[${key}]`, file );
							}
						)
					}
				);

				$.ajax(
					{
						url: woocommerce_admin_meta_boxes.ajax_url,
						data: formData,
						type: 'POST',
						contentType: false,
						processData: false,
						beforeSend: function () {
							$( '#payever-claim-upload-action' ).prop( 'disabled', true );
							$( '#claim-upload-spinner' ).addClass( 'is-active' );
						}
					}
				).done(
					function (response) {
						if (response.success) {
							window.location.reload();
						} else {
							window.alert( response.data.error );
						}
					}
				).always(
					function () {
						$( '#payever-claim-upload-action' ).prop( 'disabled', false );
						$( '#claim-upload-spinner' ).removeClass( 'is-active' );
						$( '#woocommerce-order-items' ).unblock();
					}
				);
			},
		};

		const payever_wc_claim = {
			init: function () {
				this.cacheElements();
				this.bindEvents();
			},

			cacheElements: function () {
				this.$payeverClaim = $( 'div.wc-payever-claim' );
			},

			bindEvents: function () {
				$( '#woocommerce-order-items' )
					.on( 'click', 'button.wc-payever-claim-button', this.claimUploadShow.bind( this ) )
					.on( 'click', '#payever-claim-action', this.doClaim.bind( this ) );

				this.$payeverClaim.appendTo( '#woocommerce-order-items .inside' );
			},

			claimUploadShow: function () {
				this.$payeverClaim.slideDown();
				$( 'div.wc-order-data-row-toggle' ).not( 'div.wc-payever-claim' ).slideUp();
				$( 'div.wc-order-totals-items' ).slideUp();
			},

			doClaim: function () {
				const data = {
					action: 'payever_claim',
					nonce: woocommerce_admin_meta_boxes.payever_claim_nonce,
					order_id: woocommerce_admin_meta_boxes.post_id,
					is_disputed: $('#is_disputed:checkbox:checked').val() || 0,
				};

				$.ajax(
					{
						url: woocommerce_admin_meta_boxes.ajax_url,
						data: data,
						type: 'POST',
						dataType: 'json',
						beforeSend: function () {
							$( '#payever-claim-action' ).prop( 'disabled', true );
							$( '#claim-spinner' ).addClass( 'is-active' );
						}
					}
				).done(
					function (response) {
						if (response.success) {
							window.location.reload();
						} else {
							window.alert( response.data.error );
						}
					}
				).always(
					function () {
						$( '#payever-claim-action' ).prop( 'disabled', false );
						$( '#claim-spinner' ).removeClass( 'is-active' );
						$( '#woocommerce-order-items' ).unblock();
					}
				);
			},
		};

		payever_wc_claim_upload.init();
		payever_wc_claim.init();
	}
);
