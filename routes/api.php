<?php

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Controllers\AdditionalColumnController;
use FluxErp\Http\Controllers\AddressController;
use FluxErp\Http\Controllers\AddressTypeController;
use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Controllers\BankConnectionController;
use FluxErp\Http\Controllers\CalendarController;
use FluxErp\Http\Controllers\CalendarEventController;
use FluxErp\Http\Controllers\CategoryController;
use FluxErp\Http\Controllers\ClientController;
use FluxErp\Http\Controllers\CommentController;
use FluxErp\Http\Controllers\ContactBankConnectionController;
use FluxErp\Http\Controllers\ContactController;
use FluxErp\Http\Controllers\ContactOptionController;
use FluxErp\Http\Controllers\ContactOriginController;
use FluxErp\Http\Controllers\CountryController;
use FluxErp\Http\Controllers\CountryRegionController;
use FluxErp\Http\Controllers\CurrencyController;
use FluxErp\Http\Controllers\CustomEventController;
use FluxErp\Http\Controllers\DiscountController;
use FluxErp\Http\Controllers\DocumentTypeController;
use FluxErp\Http\Controllers\EventSubscriptionController;
use FluxErp\Http\Controllers\FormBuilderFieldController;
use FluxErp\Http\Controllers\FormBuilderFieldResponseController;
use FluxErp\Http\Controllers\FormBuilderFormController;
use FluxErp\Http\Controllers\FormBuilderResponseController;
use FluxErp\Http\Controllers\FormBuilderSectionController;
use FluxErp\Http\Controllers\LanguageController;
use FluxErp\Http\Controllers\LedgerAccountController;
use FluxErp\Http\Controllers\LockController;
use FluxErp\Http\Controllers\MailAccountController;
use FluxErp\Http\Controllers\MediaController;
use FluxErp\Http\Controllers\NotificationSettingsController;
use FluxErp\Http\Controllers\OrderController;
use FluxErp\Http\Controllers\OrderPositionController;
use FluxErp\Http\Controllers\OrderTypeController;
use FluxErp\Http\Controllers\PaymentReminderController;
use FluxErp\Http\Controllers\PaymentReminderTextController;
use FluxErp\Http\Controllers\PaymentRunController;
use FluxErp\Http\Controllers\PaymentTypeController;
use FluxErp\Http\Controllers\PermissionController;
use FluxErp\Http\Controllers\PriceController;
use FluxErp\Http\Controllers\PriceListController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\ProductBundleProductController;
use FluxErp\Http\Controllers\ProductController;
use FluxErp\Http\Controllers\ProductCrossSellingController;
use FluxErp\Http\Controllers\ProductOptionController;
use FluxErp\Http\Controllers\ProductOptionGroupController;
use FluxErp\Http\Controllers\ProductPropertyController;
use FluxErp\Http\Controllers\ProjectController;
use FluxErp\Http\Controllers\PurchaseInvoiceController;
use FluxErp\Http\Controllers\PurchaseInvoicePositionController;
use FluxErp\Http\Controllers\RoleController;
use FluxErp\Http\Controllers\SepaMandateController;
use FluxErp\Http\Controllers\SerialNumberController;
use FluxErp\Http\Controllers\SerialNumberRangeController;
use FluxErp\Http\Controllers\SettingController;
use FluxErp\Http\Controllers\StockPostingController;
use FluxErp\Http\Controllers\TagController;
use FluxErp\Http\Controllers\TaskController;
use FluxErp\Http\Controllers\TicketController;
use FluxErp\Http\Controllers\TicketTypeController;
use FluxErp\Http\Controllers\TimeTrackingController;
use FluxErp\Http\Controllers\TimeTrackingTypeController;
use FluxErp\Http\Controllers\TranslationController;
use FluxErp\Http\Controllers\UnitController;
use FluxErp\Http\Controllers\UserController;
use FluxErp\Http\Controllers\ValueListController;
use FluxErp\Http\Controllers\VatRateController;
use FluxErp\Http\Controllers\WarehouseController;
use FluxErp\Http\Middleware\SetAcceptHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('api')
    ->middleware(['throttle:api', SetAcceptHeaders::class])
    ->group(function (): void {
        Route::get('/media/{filename}', [MediaController::class, 'downloadPublic'])->name('media.public');
        Route::post('/auth/token', [AuthController::class, 'authenticate']);

        Route::middleware(['auth:sanctum', 'abilities:user', 'localization', 'permission', 'api'])
            ->name('api.')
            ->group(function (): void {
                // Validate Token
                Route::get('/auth/token/validate', [AuthController::class, 'validateToken']);
                Route::post('/logout', [AuthController::class, 'logout']);

                // AdditionalColumns
                Route::get('/additional-columns/{id}', [AdditionalColumnController::class, 'show']);
                Route::get('/additional-columns', [AdditionalColumnController::class, 'index']);
                Route::post('/additional-columns', [AdditionalColumnController::class, 'create']);
                Route::put('/additional-columns', [AdditionalColumnController::class, 'update']);
                Route::delete('/additional-columns/{id}', [AdditionalColumnController::class, 'delete']);

                // Addresses
                Route::get('/addresses/{id}', [AddressController::class, 'show']);
                Route::get('/addresses', [AddressController::class, 'index']);
                Route::post('/addresses', [AddressController::class, 'create']);
                Route::put('/addresses', [AddressController::class, 'update']);
                Route::delete('/addresses/{id}', [AddressController::class, 'delete']);
                Route::post('/address/{address}/login-token', [AddressController::class, 'generateLoginToken']);

                // AddressTypes
                Route::get('/address-types/{id}', [AddressTypeController::class, 'show']);
                Route::get('/address-types', [AddressTypeController::class, 'index']);
                Route::post('/address-types', [AddressTypeController::class, 'create']);
                Route::put('/address-types', [AddressTypeController::class, 'update']);
                Route::delete('/address-types/{id}', [AddressTypeController::class, 'delete']);

                // BankConnections
                Route::get('/bank-connections/{id}', [BankConnectionController::class, 'show']);
                Route::get('/bank-connections', [BankConnectionController::class, 'index']);
                Route::post('/bank-connections', [BankConnectionController::class, 'create']);
                Route::put('/bank-connections', [BankConnectionController::class, 'update']);
                Route::delete('/bank-connections/{id}', [BankConnectionController::class, 'delete']);

                // Calendars
                Route::get('/calendars/{id}', [CalendarController::class, 'show']);
                Route::get('/calendars', [CalendarController::class, 'index']);
                Route::post('/calendars', [CalendarController::class, 'create']);
                Route::put('/calendars', [CalendarController::class, 'update']);
                Route::delete('/calendars/{id}', [CalendarController::class, 'delete']);

                // CalendarEvents
                Route::get('/calendar-events/{id}', [CalendarEventController::class, 'show']);
                Route::get('/calendar-events', [CalendarEventController::class, 'index']);
                Route::post('/calendar-events', [CalendarEventController::class, 'create']);
                Route::put('/calendar-events', [CalendarEventController::class, 'update']);
                Route::delete('/calendar-events/{id}', [CalendarEventController::class, 'delete']);

                // Categories
                Route::get('/categories/{id}', [CategoryController::class, 'show']);
                Route::get('/categories', [CategoryController::class, 'index']);
                Route::post('/categories', [CategoryController::class, 'create']);
                Route::put('/categories', [CategoryController::class, 'update']);
                Route::delete('/categories/{id}', [CategoryController::class, 'delete']);

                // Clients
                Route::get('/clients/{id}', [ClientController::class, 'show']);
                Route::get('/clients', [ClientController::class, 'index']);

                // Comments
                Route::get('/{modelType}/comments/{id}', [CommentController::class, 'show']);
                Route::post('/comments', [CommentController::class, 'create']);
                Route::put('/comments', [CommentController::class, 'update']);
                Route::delete('/comments/{id}', [CommentController::class, 'delete']);

                // ContactBankConnections
                Route::get('/contact-bank-connections/{id}', [ContactBankConnectionController::class, 'show']);
                Route::get('/contact-bank-connections', [ContactBankConnectionController::class, 'index']);
                Route::post('/contact-bank-connections', [ContactBankConnectionController::class, 'create']);
                Route::put('/contact-bank-connections', [ContactBankConnectionController::class, 'update']);
                Route::delete('/contact-bank-connections/{id}', [ContactBankConnectionController::class, 'delete']);

                // ContactOptions
                Route::get('/contact-options/{id}', [ContactOptionController::class, 'index']);
                Route::post('/contact-options', [ContactOptionController::class, 'create']);
                Route::put('/contact-options', [ContactOptionController::class, 'update']);
                Route::delete('/contact-options', [ContactOptionController::class, 'delete']);

                // ContactOrigins
                Route::get('/contact-origins/{id}', [ContactOriginController::class, 'show']);
                Route::get('/contact-origins', [ContactOriginController::class, 'index']);
                Route::post('/contact-origins', [ContactOriginController::class, 'create']);
                Route::put('/contact-origins', [ContactOriginController::class, 'update']);
                Route::delete('/contact-origins/{id}', [ContactOriginController::class, 'delete']);

                // Contacts
                Route::get('/contacts/{id}', [ContactController::class, 'show']);
                Route::get('/contacts', [ContactController::class, 'index']);
                Route::post('/contacts', [ContactController::class, 'create']);
                Route::put('/contacts', [ContactController::class, 'update']);
                Route::delete('/contacts/{id}', [ContactController::class, 'delete']);

                // Countries
                Route::get('/countries/{id}', [CountryController::class, 'show']);
                Route::get('/countries', [CountryController::class, 'index']);
                Route::post('/countries', [CountryController::class, 'create']);
                Route::put('/countries', [CountryController::class, 'update']);
                Route::delete('/countries/{id}', [CountryController::class, 'delete']);

                // CountryRegions
                Route::get('/country-regions/{id}', [CountryRegionController::class, 'show']);
                Route::get('/country-regions', [CountryRegionController::class, 'index']);
                Route::post('/country-regions', [CountryRegionController::class, 'create']);
                Route::put('/country-regions', [CountryRegionController::class, 'update']);
                Route::delete('/country-regions/{id}', [CountryRegionController::class, 'delete']);

                // Currencies
                Route::get('/currencies/{id}', [CurrencyController::class, 'show']);
                Route::get('/currencies', [CurrencyController::class, 'index']);
                Route::post('/currencies', [CurrencyController::class, 'create']);
                Route::put('/currencies', [CurrencyController::class, 'update']);
                Route::delete('/currencies/{id}', [CurrencyController::class, 'delete']);

                // CustomEvents
                Route::get('/custom-events/{id}', [CustomEventController::class, 'show']);
                Route::get('/custom-events', [CustomEventController::class, 'index']);
                Route::post('/custom-events', [CustomEventController::class, 'create']);
                Route::put('/custom-events', [CustomEventController::class, 'update']);
                Route::delete('/custom-events/{id}', [CustomEventController::class, 'delete']);
                Route::post('/custom-events/dispatch', [CustomEventController::class, 'dispatchCustomEvent']);

                // DocumentTypes
                Route::get('/document-types/{id}', [DocumentTypeController::class, 'show']);
                Route::get('/document-types', [DocumentTypeController::class, 'index']);
                Route::post('/document-types', [DocumentTypeController::class, 'create']);
                Route::put('/document-types', [DocumentTypeController::class, 'update']);
                Route::delete('/document-types/{id}', [DocumentTypeController::class, 'delete']);

                // Discounts
                Route::get('/discounts/{id}', [DiscountController::class, 'show']);
                Route::get('/discounts', [DiscountController::class, 'index']);
                Route::post('/discounts', [DiscountController::class, 'create']);
                Route::put('/discounts', [DiscountController::class, 'update']);
                Route::delete('/discounts/{id}', [DiscountController::class, 'delete']);

                // Events
                Route::get('/events', [EventSubscriptionController::class, 'getEvents']);

                // FormBuilderForm
                Route::get('/form-builder/forms/{id}', [FormBuilderFormController::class, 'show']);
                Route::get('/form-builder/forms', [FormBuilderFormController::class, 'index']);
                Route::post('/form-builder/forms', [FormBuilderFormController::class, 'create']);
                Route::put('/form-builder/forms', [FormBuilderFormController::class, 'update']);
                Route::delete('/form-builder/forms/{id}', [FormBuilderFormController::class, 'delete']);

                // FormBuilderSection
                Route::get('/form-builder/sections/{id}', [FormBuilderSectionController::class, 'show']);
                Route::get('/form-builder/sections', [FormBuilderSectionController::class, 'index']);
                Route::post('/form-builder/sections', [FormBuilderSectionController::class, 'create']);
                Route::put('/form-builder/sections', [FormBuilderSectionController::class, 'update']);
                Route::delete('/form-builder/sections/{id}', [FormBuilderSectionController::class, 'delete']);

                // FormBuilderField
                Route::get('/form-builder/fields/{id}', [FormBuilderFieldController::class, 'show']);
                Route::get('/form-builder/fields', [FormBuilderFieldController::class, 'index']);
                Route::post('/form-builder/fields', [FormBuilderFieldController::class, 'create']);
                Route::put('/form-builder/fields', [FormBuilderFieldController::class, 'update']);
                Route::delete('/form-builder/fields/{id}', [FormBuilderFieldController::class, 'delete']);

                // FormBuilderResponse
                Route::get('/form-builder/responses/{id}', [FormBuilderResponseController::class, 'show']);
                Route::get('/form-builder/responses', [FormBuilderResponseController::class, 'index']);
                Route::post('/form-builder/responses', [FormBuilderResponseController::class, 'create']);
                Route::delete('/form-builder/responses/{id}', [FormBuilderResponseController::class, 'delete']);

                // FormBuilderFieldsResponse
                Route::get('/form-builder/fields-responses/{id}', [FormBuilderFieldResponseController::class, 'show']);
                Route::get('/form-builder/fields-responses', [FormBuilderFieldResponseController::class, 'index']);
                Route::post('/form-builder/fields-responses', [FormBuilderFieldResponseController::class, 'create']);
                Route::put('/form-builder/fields-responses', [FormBuilderFieldResponseController::class, 'update']);
                Route::delete('/form-builder/fields-responses/{id}', [FormBuilderFieldResponseController::class, 'delete']);

                // Languages
                Route::get('/languages/{id}', [LanguageController::class, 'show']);
                Route::get('/languages', [LanguageController::class, 'index']);
                Route::post('/languages', [LanguageController::class, 'create']);
                Route::put('/languages', [LanguageController::class, 'update']);
                Route::delete('/languages/{id}', [LanguageController::class, 'delete']);

                // LedgerAccounts
                Route::get('/ledger-accounts/{id}', [LedgerAccountController::class, 'show']);
                Route::get('/ledger-accounts', [LedgerAccountController::class, 'index']);
                Route::post('/ledger-accounts', [LedgerAccountController::class, 'create']);
                Route::put('/ledger-accounts', [LedgerAccountController::class, 'update']);
                Route::delete('/ledger-accounts/{id}', [LedgerAccountController::class, 'delete']);

                // Locking
                Route::get('/user/locks', [LockController::class, 'showUserLocks']);
                Route::get('/locks', [LockController::class, 'index']);
                Route::get('/{modelType}/lock', [LockController::class, 'lock']);

                // MailAccounts
                Route::get('/mail-accounts/{id}', [MailAccountController::class, 'show']);
                Route::get('/mail-accounts', [MailAccountController::class, 'index']);
                Route::post('/mail-accounts', [MailAccountController::class, 'create']);
                Route::put('/mail-accounts', [MailAccountController::class, 'update']);
                Route::delete('/mail-accounts/{id}', [MailAccountController::class, 'delete']);

                // Media
                Route::get('/media/private/{id}', [MediaController::class, 'download'])
                    ->withoutMiddleware(SetAcceptHeaders::class);
                Route::post('/media/{id}', [MediaController::class, 'replace']);
                Route::post('/media', [MediaController::class, 'upload']);
                Route::put('/media', [MediaController::class, 'update']);
                Route::delete('/media/{id}', [MediaController::class, 'delete']);
                Route::delete('/media-collection', [MediaController::class, 'deleteCollection']);

                // NotificationSettings
                Route::put('/notifications', [NotificationSettingsController::class, 'update']);
                Route::put('/user/notifications', [NotificationSettingsController::class, 'updateUserNotifications']);

                // Orders
                Route::get('/orders/{id}', [OrderController::class, 'show']);
                Route::get('/orders', [OrderController::class, 'index']);
                Route::post('/orders', [OrderController::class, 'create']);
                Route::put('/orders', [OrderController::class, 'update']);
                Route::put('/orders/{id}/toggle-lock', [OrderController::class, 'toggleLock']);
                Route::delete('/orders/{id}', [OrderController::class, 'delete']);

                // OrderPositions
                Route::get('/order-positions/{id}', [OrderPositionController::class, 'show']);
                Route::get('/order-positions', [OrderPositionController::class, 'index']);
                Route::post('/order-positions', [OrderPositionController::class, 'create']);
                Route::post('/order-positions/fill', [OrderPositionController::class, 'fill']);
                Route::put('/order-positions', [OrderPositionController::class, 'update']);
                Route::delete('/order-positions/{id}', [OrderPositionController::class, 'delete']);

                // OrderTypes
                Route::get('/order-types/{id}', [OrderTypeController::class, 'show']);
                Route::get('/order-types', [OrderTypeController::class, 'index']);
                Route::post('/order-types', [OrderTypeController::class, 'create']);
                Route::put('/order-types', [OrderTypeController::class, 'update']);
                Route::delete('/order-types/{id}', [OrderTypeController::class, 'delete']);

                // PaymentReminders
                Route::get('/payment-reminders/{id}', [PaymentReminderController::class, 'show']);
                Route::get('/payment-reminders', [PaymentReminderController::class, 'index']);
                Route::post('/payment-reminders', [PaymentReminderController::class, 'create']);
                Route::put('/payment-reminders', [PaymentReminderController::class, 'update']);
                Route::delete('/payment-reminders/{id}', [PaymentReminderController::class, 'delete']);

                // PaymentReminderTexts
                Route::get('/payment-reminder-texts/{id}', [PaymentReminderTextController::class, 'show']);
                Route::get('/payment-reminder-texts', [PaymentReminderTextController::class, 'index']);
                Route::post('/payment-reminder-texts', [PaymentReminderTextController::class, 'create']);
                Route::put('/payment-reminder-texts', [PaymentReminderTextController::class, 'update']);
                Route::delete('/payment-reminder-texts/{id}', [PaymentReminderTextController::class, 'delete']);

                // PaymentRuns
                Route::get('/payment-runs/{id}', [PaymentRunController::class, 'show']);
                Route::get('/payment-runs', [PaymentRunController::class, 'index']);
                Route::post('/payment-runs', [PaymentRunController::class, 'create']);
                Route::put('/payment-runs', [PaymentRunController::class, 'update']);
                Route::delete('/payment-runs/{id}', [PaymentRunController::class, 'delete']);

                // PaymentTypes
                Route::get('/payment-types/{id}', [PaymentTypeController::class, 'show']);
                Route::get('/payment-types', [PaymentTypeController::class, 'index']);
                Route::post('/payment-types', [PaymentTypeController::class, 'create']);
                Route::put('/payment-types', [PaymentTypeController::class, 'update']);
                Route::delete('/payment-types/{id}', [PaymentTypeController::class, 'delete']);

                // Permissions
                Route::get('/permissions', [PermissionController::class, 'index']);
                Route::get('/permissions/user/{id}', [PermissionController::class, 'showUserPermissions']);
                Route::post('/permissions', [PermissionController::class, 'create']);
                Route::put('/permissions/give', [PermissionController::class, 'give']);
                Route::put('/permissions/revoke', [PermissionController::class, 'revoke']);
                Route::put('/permissions/sync', [PermissionController::class, 'sync']);
                Route::delete('/permissions/{id}', [PermissionController::class, 'delete']);

                // Prices
                Route::get('/prices/{id}', [PriceController::class, 'show']);
                Route::get('/prices', [PriceController::class, 'index']);
                Route::post('/prices', [PriceController::class, 'create']);
                Route::put('/prices', [PriceController::class, 'update']);
                Route::delete('/prices/{id}', [PriceController::class, 'delete']);

                // PriceLists
                Route::get('/price-lists/{id}', [PriceListController::class, 'show']);
                Route::get('/price-lists', [PriceListController::class, 'index']);
                Route::post('/price-lists', [PriceListController::class, 'create']);
                Route::put('/price-lists', [PriceListController::class, 'update']);
                Route::delete('/price-lists/{id}', [PriceListController::class, 'delete']);

                // PrintPdf
                Route::post('/print/views', [PrintController::class, 'getPrintViews']);
                Route::post('/print/render', [PrintController::class, 'render']);
                Route::post('/print/pdf', [PrintController::class, 'renderPdf']);

                // Products
                Route::get('/products/{id}', [ProductController::class, 'show']);
                Route::get('/products', [ProductController::class, 'index']);
                Route::post('/products', [ProductController::class, 'create']);
                Route::put('/products', [ProductController::class, 'update']);
                Route::delete('/products/{id}', [ProductController::class, 'delete']);

                // Product bundle products
                Route::get('/product-bundle-products/{id}', [ProductBundleProductController::class, 'show']);
                Route::get('/product-bundle-products', [ProductBundleProductController::class, 'index']);
                Route::post('/product-bundle-products', [ProductBundleProductController::class, 'create']);
                Route::put('/product-bundle-products', [ProductBundleProductController::class, 'update']);
                Route::delete('/product-bundle-products/{id}', [ProductBundleProductController::class, 'delete']);

                // ProductCrossSellings
                Route::get('/product-cross-sellings/{id}', [ProductCrossSellingController::class, 'show']);
                Route::get('/product-cross-sellings', [ProductCrossSellingController::class, 'index']);
                Route::post('/product-cross-sellings', [ProductCrossSellingController::class, 'create']);
                Route::put('/product-cross-sellings', [ProductCrossSellingController::class, 'update']);
                Route::delete('/product-cross-sellings/{id}', [ProductCrossSellingController::class, 'delete']);

                // ProductOptions
                Route::get('/product-options/{id}', [ProductOptionController::class, 'show']);
                Route::get('/product-options', [ProductOptionController::class, 'index']);
                Route::post('/product-options', [ProductOptionController::class, 'create']);
                Route::put('/product-options', [ProductOptionController::class, 'update']);
                Route::delete('/product-options/{id}', [ProductOptionController::class, 'delete']);

                // ProductOptionGroups
                Route::get('/product-option-groups/{id}', [ProductOptionGroupController::class, 'show']);
                Route::get('/product-option-groups', [ProductOptionGroupController::class, 'index']);
                Route::post('/product-option-groups', [ProductOptionGroupController::class, 'create']);
                Route::put('/product-option-groups', [ProductOptionGroupController::class, 'update']);
                Route::delete('/product-option-groups/{id}', [ProductOptionGroupController::class, 'delete']);

                // ProductProperties
                Route::get('/product-properties/{id}', [ProductPropertyController::class, 'show']);
                Route::get('/product-properties/', [ProductPropertyController::class, 'index']);
                Route::post('/product-properties/', [ProductPropertyController::class, 'create']);
                Route::put('/product-properties/', [ProductPropertyController::class, 'update']);
                Route::delete('/product-properties/{id}', [ProductPropertyController::class, 'delete']);

                // Projects
                Route::get('/projects/{id}', [ProjectController::class, 'show']);
                Route::get('/projects', [ProjectController::class, 'index']);
                Route::post('/projects', [ProjectController::class, 'create']);
                Route::put('/projects', [ProjectController::class, 'update']);
                Route::delete('/projects/{id}', [ProjectController::class, 'delete']);
                Route::post('/projects/finish', [ProjectController::class, 'finish']);

                // PurchaseInvoices
                Route::get('/purchase-invoices/{id}', [PurchaseInvoiceController::class, 'show']);
                Route::get('/purchase-invoices', [PurchaseInvoiceController::class, 'index']);
                Route::post('/purchase-invoices', [PurchaseInvoiceController::class, 'create']);
                Route::put('/purchase-invoices', [PurchaseInvoiceController::class, 'update']);
                Route::delete('/purchase-invoices/{id}', [PurchaseInvoiceController::class, 'delete']);
                Route::post('/purchase-invoices/finish', [PurchaseInvoiceController::class, 'finish']);

                // PurchaseInvoicePositions
                Route::get('/purchase-invoice-positions/{id}', [PurchaseInvoicePositionController::class, 'show']);
                Route::get('/purchase-invoice-positions', [PurchaseInvoicePositionController::class, 'index']);
                Route::post('/purchase-invoice-positions', [PurchaseInvoicePositionController::class, 'create']);
                Route::put('/purchase-invoice-positions', [PurchaseInvoicePositionController::class, 'update']);
                Route::delete('/purchase-invoice-positions/{id}', [PurchaseInvoicePositionController::class, 'delete']);

                // Roles
                Route::get('/roles', [RoleController::class, 'index']);
                Route::get('/roles/{id}', [RoleController::class, 'show']);
                Route::get('/roles/user/{id}', [RoleController::class, 'showUserRoles']);
                Route::post('/roles', [RoleController::class, 'create']);
                Route::put('/roles', [RoleController::class, 'update']);
                Route::put('/roles/give', [RoleController::class, 'give']);
                Route::put('/roles/revoke', [RoleController::class, 'revoke']);
                Route::put('/roles/users/assign', [RoleController::class, 'assignUsers']);
                Route::put('/roles/users/revoke', [RoleController::class, 'revokeUsers']);
                Route::put('/roles/users/sync', [RoleController::class, 'syncUserRoles']);
                Route::delete('/roles/{id}', [RoleController::class, 'delete']);

                // SepaMandates
                Route::get('/sepa-mandates/{id}', [SepaMandateController::class, 'show']);
                Route::get('/sepa-mandates', [SepaMandateController::class, 'index']);
                Route::post('/sepa-mandates', [SepaMandateController::class, 'create']);
                Route::put('/sepa-mandates', [SepaMandateController::class, 'update']);
                Route::delete('/sepa-mandates/{id}', [SepaMandateController::class, 'delete']);

                // SerialNumberRanges
                Route::get('/serial-number-ranges/{id}', [SerialNumberRangeController::class, 'show']);
                Route::get('/serial-number-ranges', [SerialNumberRangeController::class, 'index']);
                Route::post('/serial-number-ranges', [SerialNumberRangeController::class, 'create']);
                Route::put('/serial-number-ranges', [SerialNumberRangeController::class, 'update']);
                Route::delete('/serial-number-ranges/{id}', [SerialNumberRangeController::class, 'delete']);

                // SerialNumbers
                Route::get('/serial-numbers/{id}', [SerialNumberController::class, 'show']);
                Route::get('/serial-numbers', [SerialNumberController::class, 'index']);
                Route::post('/serial-numbers', [SerialNumberController::class, 'create']);
                Route::put('/serial-numbers', [SerialNumberController::class, 'update']);
                Route::delete('/serial-numbers/{id}', [SerialNumberController::class, 'delete']);

                // Settings
                Route::get('/settings', [SettingController::class, 'index']);
                Route::post('/settings', [SettingController::class, 'create']);
                Route::put('/settings', [SettingController::class, 'update']);

                // Subscriptions
                Route::get('/event-subscriptions', [EventSubscriptionController::class, 'index']);
                Route::get('/event-subscriptions/user', [EventSubscriptionController::class, 'getUserSubscriptions']);
                Route::post('/event-subscriptions', [EventSubscriptionController::class, 'create']);
                Route::put('/event-subscriptions', [EventSubscriptionController::class, 'update']);
                Route::delete('/event-subscriptions/{id}', [EventSubscriptionController::class, 'delete']);

                // StockPostings
                Route::get('/stock-postings/{id}', [StockPostingController::class, 'show']);
                Route::get('/stock-postings', [StockPostingController::class, 'index']);
                Route::post('/stock-postings', [StockPostingController::class, 'create']);
                Route::delete('/stock-postings/{id}', [StockPostingController::class, 'delete']);

                // Tag
                Route::get('/tags/{id}', [TagController::class, 'show']);
                Route::get('/tags', [TagController::class, 'index']);
                Route::post('/tags', [TagController::class, 'create']);
                Route::put('/tags', [TagController::class, 'update']);
                Route::delete('/tags/{id}', [TagController::class, 'delete']);

                // Tasks
                Route::get('/tasks/{id}', [TaskController::class, 'show']);
                Route::get('/tasks', [TaskController::class, 'index']);
                Route::post('/tasks', [TaskController::class, 'create']);
                Route::put('/tasks', [TaskController::class, 'update']);
                Route::delete('/tasks/{id}', [TaskController::class, 'delete']);
                Route::post('/tasks/finish', [TaskController::class, 'finish']);

                // Tickets
                Route::post('/tickets/toggle/', [TicketController::class, 'toggleUserAssignment']);
                Route::get('/tickets/{id}', [TicketController::class, 'show']);
                Route::get('/tickets', [TicketController::class, 'index']);
                Route::post('/tickets', [TicketController::class, 'create']);
                Route::put('/tickets', [TicketController::class, 'update']);
                Route::delete('/tickets/{id}', [TicketController::class, 'delete']);

                // TicketTypes
                Route::get('/ticket-types/{id}', [TicketTypeController::class, 'show']);
                Route::get('/ticket-types', [TicketTypeController::class, 'index']);
                Route::post('/ticket-types', [TicketTypeController::class, 'create']);
                Route::put('/ticket-types', [TicketTypeController::class, 'update']);
                Route::delete('/ticket-types/{id}', [TicketTypeController::class, 'delete']);

                // TimeTracking
                Route::get('/user/time-tracking', [TimeTrackingController::class, 'userIndex']);
                Route::get('/time-tracking', [TimeTrackingController::class, 'index']);
                Route::post('/time-tracking', [TimeTrackingController::class, 'create']);
                Route::put('/time-tracking', [TimeTrackingController::class, 'update']);
                Route::delete('/time-tracking/{id}', [TimeTrackingController::class, 'delete']);

                // TimeTrackingTypes
                Route::get('/time-tracking-types', [TimeTrackingTypeController::class, 'index']);
                Route::post('/time-tracking-types', [TimeTrackingTypeController::class, 'create']);
                Route::put('/time-tracking-types', [TimeTrackingTypeController::class, 'update']);
                Route::delete('/time-tracking-types/{id}', [TimeTrackingTypeController::class, 'delete']);

                // Translations
                Route::get('/translations', [TranslationController::class, 'index']);
                Route::post('/translations', [TranslationController::class, 'create']);
                Route::put('/translations', [TranslationController::class, 'update']);
                Route::delete('/translations/{id}', [TranslationController::class, 'delete']);

                // Units
                Route::get('/units/{id}', [UnitController::class, 'show']);
                Route::get('/units', [UnitController::class, 'index']);
                Route::post('/units', [UnitController::class, 'create']);
                Route::delete('/units/{id}', [UnitController::class, 'delete']);

                // Users
                Route::get('/user/settings', [SettingController::class, 'getUserSettings']);
                Route::get('/user', function (Request $request) {
                    $user = $request->user();
                    $user->permissions = $request->user()->permissions;

                    return ResponseHelper::createResponseFromBase(statusCode: 200, data: $user);
                });

                Route::get('/users/{id}', [UserController::class, 'show']);
                Route::get('/users', [UserController::class, 'index']);
                Route::post('/users', [UserController::class, 'create']);
                Route::put('/users', [UserController::class, 'update']);
                Route::delete('/users/{id}', [UserController::class, 'delete']);

                // ValueLists
                Route::get('/value-lists/{id}', [ValueListController::class, 'show']);
                Route::get('/value-lists', [ValueListController::class, 'index']);
                Route::post('/value-lists', [ValueListController::class, 'create']);
                Route::put('/value-lists', [ValueListController::class, 'update']);
                Route::delete('/value-lists/{id}', [ValueListController::class, 'delete']);

                // VatRates
                Route::get('/vat-rates/{id}', [VatRateController::class, 'show']);
                Route::get('/vat-rates', [VatRateController::class, 'index']);
                Route::post('/vat-rates', [VatRateController::class, 'create']);
                Route::put('/vat-rates', [VatRateController::class, 'update']);
                Route::delete('/vat-rates/{id}', [VatRateController::class, 'delete']);

                // Warehouses
                Route::get('/warehouses/{id}', [WarehouseController::class, 'show']);
                Route::get('/warehouses', [WarehouseController::class, 'index']);
                Route::post('/warehouses', [WarehouseController::class, 'create']);
                Route::put('/warehouses', [WarehouseController::class, 'update']);
                Route::delete('/warehouses/{id}', [WarehouseController::class, 'delete']);
            });

        Broadcast::routes(['middleware' => ['auth:sanctum']]);
    });
