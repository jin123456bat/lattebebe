<?php
// Heading
$_['heading_title']					 = 'Realex Redirect';

// Text
$_['text_extension']				 = 'Estensioni';
$_['text_success']					= 'Realex account details modificato correttamente!';
$_['text_edit']                     = 'Edit Realex Redirect';
$_['text_live']						= 'Live';
$_['text_demo']						= 'Demo';
$_['text_card_type']				= 'Tipo Carta';
$_['text_enabled']					= 'Abilitato';
$_['text_use_default']				= 'Use default';
$_['text_merchant_id']				= 'ID Commerciante';
$_['text_subaccount']				= 'Subaccount';
$_['text_secret']					= 'Shared secret';
$_['text_card_visa']				= 'Visa';
$_['text_card_master']				= 'Mastercard';
$_['text_card_amex']				= 'American Express';
$_['text_card_switch']				= 'Switch/Maestro';
$_['text_card_laser']				= 'Laser';
$_['text_card_diners']				= 'Diners';
$_['text_capture_ok']				= 'Acquisizione effettuata';
$_['text_capture_ok_order']			= 'Acquisizione effettuata, stato ordine aggiornato con - settled';
$_['text_rebate_ok']				= 'Rimborso effettuato';
$_['text_rebate_ok_order']			= 'Riduzione avvenuta con successo. Stato ordine aggiornato come Ridotto';
$_['text_void_ok']					= 'Annullamento avvenuto con successo. Stato aggiornato come Annullato';
$_['text_settle_auto']				= 'Auto';
$_['text_settle_delayed']			= 'Differito';
$_['text_settle_multi']				= 'Multi';
$_['text_url_message']				= 'Specificare indirizzo URL del negozio nel gestore account Realex prima di andare live';
$_['text_payment_info']				= 'Informazioni Pagamento';
$_['text_capture_status']			= 'Pagamento Acquisito';
$_['text_void_status']				= 'Pagamento Annullato';
$_['text_rebate_status']			= 'Pagamento Ridotto';
$_['text_order_ref']				= 'Rif ordine';
$_['text_order_total']				= 'Totale Autorizzato';
$_['text_total_captured']			= 'Totale Acquisito';
$_['text_transactions']				= 'Transazioni';
$_['text_column_amount']			= 'Importo';
$_['text_column_type']				= 'Tipo';
$_['text_column_date_added']		= 'Creato';
$_['text_confirm_void']				= 'Sicuri di voler annullare il pagamento?';
$_['text_confirm_capture']			= 'Sicuri di voler acquisire il pagamento?';
$_['text_confirm_rebate']			= 'Sicuri di voler rimborsare il pagamento?';
$_['text_realex']					= '<a target="_BLANK" href="http://www.realexpayments.co.uk/partner-refer?id=opencart"><img src="view/image/payment/realex.png" alt="Realex" title="Realex" style="border: 1px solid #EEEEEE;" /></a>';

// Entry
$_['entry_merchant_id']				= 'ID Commerciante';
$_['entry_secret']					= 'Shared secret';
$_['entry_rebate_password']			= 'Password Riduzione';
$_['entry_total']					= 'Totale';
$_['entry_sort_order']				= 'Ordinamento';
$_['entry_geo_zone']				= 'Zona Geografica';
$_['entry_status']					= 'Stato';
$_['entry_debug']					= 'Salvataggio Debug';
$_['entry_live_demo']				= 'Live / Demo';
$_['entry_auto_settle']				= 'Tipo di Deposito';
$_['entry_card_select']				= 'Seleziona Carta';
$_['entry_tss_check']				= 'TSS checks';
$_['entry_live_url']				= 'URL di connessione Live ';
$_['entry_demo_url']				= 'URL di connessione Demo';
$_['entry_status_success_settled']	= 'Successo - depositato';
$_['entry_status_success_unsettled'] = 'Successo - non depositato';
$_['entry_status_decline']			= 'Declina';
$_['entry_status_decline_pending']	= 'Declinato - autorizzazione offline';
$_['entry_status_decline_stolen']	= 'Declinato - carta persa o rubata';
$_['entry_status_decline_bank']		= 'Declinato - errore banca';
$_['entry_status_void']				= 'Annulla';
$_['entry_status_rebate']			= 'Rimborsato';
$_['entry_notification_url']		= 'URL di Notifica';

// Help
$_['help_total']					= 'Il totale che deve raggiungere l\'ordine prima che diventi attiva questa tipologia di pagamento.';
$_['help_card_select']				= 'Chiedi all\'utente di scegliere il tipo di carta prima di reindirizzarlo';
$_['help_notification']				= 'Si deve fornire questo URL a Realex per avere notifiche di pagamento';
$_['help_debug']					= 'Abilitando il debug verranno scritti dati sensibili in un file. Si consiglia di mantenere sempre disabilitato se non indicato diversamente';
$_['help_dcc_settle']				= 'Se il subaccount &egrave; abilitato DCC si deve utilizzare Autosettle';

// Tab
$_['tab_api']					= 'Informazioni API';
$_['tab_account']					= 'Account';
$_['tab_order_status']				= 'Stato ordine';
$_['tab_payment']					= 'Informazioni Pagamento';
$_['tab_advanced']					= 'Avanzate';

// Button
$_['button_capture']				= 'Acquisizione';
$_['button_rebate']					= 'Rimborso';
$_['button_void']					= 'Annulla';

// Error
$_['error_merchant_id']				= 'ID Commerciante obbligatorio';
$_['error_secret']					= 'Shared secret obbligatorio';
$_['error_live_url']				= 'Live URL obbligatorio';
$_['error_demo_url']				= 'Demo URL obbligatorio';
$_['error_data_missing']			= 'Dati Mancanti';
$_['error_use_select_card']			= '"Seleziona carta" deve essere abilitato per rendere funzionante l\'instradamento dei subaccount per tipo di carta';