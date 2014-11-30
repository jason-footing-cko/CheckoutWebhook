using System;
using System.IO;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Globalization;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.Routing;
using Nop.Core;
using Nop.Core.Domain.Catalog;
using Nop.Core.Domain.Directory;
using Nop.Core.Domain.Orders;
using Nop.Core.Domain.Payments;
using Nop.Core.Plugins;
using Nop.Services.Configuration;
using Nop.Services.Customers;
using Nop.Services.Directory;
using Nop.Services.Localization;
using Nop.Services.Orders;
using Nop.Services.Payments;
using Nop.Services.Security;
using Nop.Plugin.Payments.Checkoutapipayment.Controllers;
using Nop.Plugin.Payments.Checkoutapipayment.DataTypes;
using Nop.Plugin.Payments.Checkoutapipayment.GatewayRequests;

namespace Nop.Plugin.Payments.Checkoutapipayment
{
    public class CheckoutapipaymentPaymentProcessor : BasePlugin, IPaymentMethod
    {
        #region Fields

        private readonly CheckoutapipaymentPaymentSettings _checkoutapipaymentPaymentSettings;
        private readonly ISettingService _settingService;
        private readonly ICurrencyService _currencyService;
        private readonly ICustomerService _customerService;
        private readonly CurrencySettings _currencySettings;
        private readonly IWebHelper _webHelper;
        private readonly IOrderTotalCalculationService _orderTotalCalculationService;
        private readonly IEncryptionService _encryptionService;

        #endregion

        #region Ctor

       public CheckoutapipaymentPaymentProcessor(CheckoutapipaymentPaymentSettings checkoutapipaymentPaymentSettings,
            ISettingService settingService,
            ICurrencyService currencyService,
            ICustomerService customerService,
            CurrencySettings currencySettings, IWebHelper webHelper,
            IOrderTotalCalculationService orderTotalCalculationService, IEncryptionService encryptionService)
        {
            this._checkoutapipaymentPaymentSettings = checkoutapipaymentPaymentSettings;
            this._settingService = settingService;
            this._currencyService = currencyService;
            this._customerService = customerService;
            this._currencySettings = currencySettings;
            this._webHelper = webHelper;
            this._orderTotalCalculationService = orderTotalCalculationService;
            this._encryptionService = encryptionService;
        }

        #endregion

        #region Utilities

       private string GatewayUrl()
       {
           string GatewayUrl = "";
           switch (_checkoutapipaymentPaymentSettings.Mode)
           {
               case Mode.Development:
                   GatewayUrl = "http://dev.checkout.com/api.gw3/v1/";
                   break;
               case Mode.Preprod:
                   GatewayUrl = "http://preprod.checkout.com/api.gw3/";
                   break;
               case Mode.Live:
                   GatewayUrl = "https://api2.checkout.com/v1/";
                   break;
               default:
                   GatewayUrl = "http://dev.checkout.com/api.gw3/v1/";
                   break;
                  
           }
           return GatewayUrl;
       }
        # endregion

        #region Methods

        /// <summary>
        /// Process a payment
        /// </summary>
        /// <param name="processPaymentRequest">Payment info required for an order processing</param>
        /// <returns>Process payment result</returns>
        public ProcessPaymentResult ProcessPayment(ProcessPaymentRequest processPaymentRequest)
        {
            var result = new ProcessPaymentResult();
            //Charge by full card
            var checkoutapipaymentGateway = new GatewayConnector();
            var checkoutapipaymentRequest = new fullcardChargeRequest();

            var customer = _customerService.GetCustomerById(processPaymentRequest.CustomerId);
            

            //Send in necessary parameters to build the gateway request json
            //Convert the amount into cents
            checkoutapipaymentRequest.amount = (Convert.ToInt32(processPaymentRequest.OrderTotal) * 100).ToString();
            checkoutapipaymentRequest.autoCapTime = _checkoutapipaymentPaymentSettings.AutoCapTime;
            if (_checkoutapipaymentPaymentSettings.PaymentAction == PaymentAction.AuthorizeAndCapture)
            {
                checkoutapipaymentRequest.autoCapture = "Y";
            }
            else
            {
                checkoutapipaymentRequest.autoCapture = "N";
            }

            if (_checkoutapipaymentPaymentSettings.IsPCI)
            {
                // Send full card info
                var checkoutapipaymentFullCard = new FullCard();
                checkoutapipaymentFullCard.cvv2 = processPaymentRequest.CreditCardCvv2;
                if (processPaymentRequest.CreditCardExpireMonth < 10)
                {
                    checkoutapipaymentFullCard.expiryMonth = "0" + processPaymentRequest.CreditCardExpireMonth.ToString();
                }
                checkoutapipaymentFullCard.expiryYear = processPaymentRequest.CreditCardExpireYear.ToString();
                checkoutapipaymentFullCard.name = processPaymentRequest.CreditCardName;
                checkoutapipaymentFullCard.number = processPaymentRequest.CreditCardNumber.ToString();

                var checkoutapipaymentBilling = new Billing();
                
                checkoutapipaymentBilling.addressLine1 = customer.BillingAddress.Address1;
                checkoutapipaymentBilling.addressLine2 = customer.BillingAddress.Address2;
                checkoutapipaymentBilling.postcode = customer.BillingAddress.ZipPostalCode;
                checkoutapipaymentBilling.country = customer.BillingAddress.Country.ThreeLetterIsoCode;
                checkoutapipaymentBilling.city = customer.BillingAddress.City;
                checkoutapipaymentBilling.state = customer.BillingAddress.StateProvince.Abbreviation;
                checkoutapipaymentBilling.phone = customer.BillingAddress.PhoneNumber;

                checkoutapipaymentFullCard.billingDetails = checkoutapipaymentBilling;

                checkoutapipaymentRequest.card = checkoutapipaymentFullCard;
            }
            else {
                // using the cko-cc-token and cko-cc-email from Checkout JS

            }

            checkoutapipaymentRequest.currency = _currencyService.GetCurrencyById(_currencySettings.PrimaryStoreCurrencyId).CurrencyCode;
            checkoutapipaymentRequest.description = "Order ID: " + processPaymentRequest.OrderGuid.ToString();
            checkoutapipaymentRequest.email = customer.Email;

            var checkoutapipaymentShipping = new Shipping();
            checkoutapipaymentShipping.addressLine1 = customer.ShippingAddress.Address1;
            checkoutapipaymentShipping.addressLine2 = customer.ShippingAddress.Address2;
            checkoutapipaymentShipping.postcode = customer.ShippingAddress.ZipPostalCode;
            checkoutapipaymentShipping.country = customer.ShippingAddress.Country.ThreeLetterIsoCode;
            checkoutapipaymentShipping.city = customer.ShippingAddress.City;
            checkoutapipaymentShipping.state = customer.ShippingAddress.StateProvince.Abbreviation;
            checkoutapipaymentShipping.phone = customer.ShippingAddress.PhoneNumber;
            checkoutapipaymentShipping.recipientName = customer.ShippingAddress.FirstName + customer.ShippingAddress.LastName;

            checkoutapipaymentRequest.shippingDetails = checkoutapipaymentShipping;

            string gatewayUrl = GatewayUrl() + "charges/card";

            //For debugging - Get Gateway URL
            System.Diagnostics.Debug.WriteLine("Gateway URL:" + gatewayUrl);


            checkoutapipaymentGateway.Uri = gatewayUrl;
            checkoutapipaymentGateway.Authorization = _checkoutapipaymentPaymentSettings.SecretKey;
            GatewayResponse checkoutapipaymentResponse = checkoutapipaymentGateway.ProcessRequest(checkoutapipaymentRequest);

            //Check whether response code is a valid successful transaction
            Regex regex = new Regex(@"^1[0-9]+$");
            System.Diagnostics.Debug.WriteLine(checkoutapipaymentResponse.responseCode);
            if (regex.IsMatch(checkoutapipaymentResponse.responseCode))
            {
                if (checkoutapipaymentResponse.status == "Authorised")
                {
                    result.NewPaymentStatus = PaymentStatus.Authorized;
                }
                else
                {
                    result.NewPaymentStatus = PaymentStatus.Paid;
                }
                result.AuthorizationTransactionId = checkoutapipaymentResponse.chargeId;
                result.AuthorizationTransactionCode = checkoutapipaymentResponse.authCode;
                result.AvsResult = checkoutapipaymentResponse.avsCheck;
                // Can store risk relavant info if the risk transaction set to authorize mode
                result.AuthorizationTransactionResult = checkoutapipaymentResponse.responseMessage;
                
            }
            else
            {
                result.AuthorizationTransactionResult = checkoutapipaymentResponse.message;
                result.AuthorizationTransactionCode = checkoutapipaymentResponse.errorCode;
                result.AddError("Payment Declined. Please check your card details");
                result.AddError("Error Code: " + checkoutapipaymentResponse.errorCode + 
                    " " + checkoutapipaymentResponse.message);
                
            }
            
            return result;
        }

        /// <summary>
        /// Post process payment (used by payment gateways that require redirecting to a third-party URL)
        /// </summary>
        /// <param name="postProcessPaymentRequest">Payment info required for an order processing</param>
        public void PostProcessPayment(PostProcessPaymentRequest postProcessPaymentRequest)
        {
            //nothing
        }

        /// <summary>
        /// Gets additional handling fee
        /// </summary>
        /// <param name="cart">Shoping cart</param>
        /// <returns>Additional handling fee</returns>
        public decimal GetAdditionalHandlingFee(IList<ShoppingCartItem> cart)
        {
            return 0;
        }

        /// <summary>
        /// Captures payment
        /// </summary>
        /// <param name="capturePaymentRequest">Capture payment request</param>
        /// <returns>Capture payment result</returns>
        public CapturePaymentResult Capture(CapturePaymentRequest capturePaymentRequest)
        {
            var result = new CapturePaymentResult();
            var checkoutapipaymentGateway = new GatewayConnector();
            var checkoutapipaymentRequest = new captureChargeRequest();

            // get the chargeId for capturing
            string chargeId = capturePaymentRequest.Order.AuthorizationTransactionId;

            //build the URL
            string gatewayUrl = GatewayUrl() + "charges/" + chargeId + "/capture";
            System.Diagnostics.Debug.WriteLine("Gateway URL for Capturing: " + gatewayUrl);

            checkoutapipaymentGateway.Uri = gatewayUrl;
            checkoutapipaymentGateway.Authorization = _checkoutapipaymentPaymentSettings.SecretKey;
            checkoutapipaymentRequest.amount = (Convert.ToInt32(capturePaymentRequest.Order.OrderTotal) * 100).ToString();
            GatewayResponse checkoutapipaymentResponse = checkoutapipaymentGateway.ProcessRequest(checkoutapipaymentRequest);

            //Check whether response code is a valid successful transaction
            Regex regex = new Regex(@"^1[0-9]+$");
            if (regex.IsMatch(checkoutapipaymentResponse.responseCode))
            {
                if (checkoutapipaymentResponse.status == "Captured"){
                    result.NewPaymentStatus = PaymentStatus.Paid;
                }
                    
                result.CaptureTransactionId = checkoutapipaymentResponse.chargeId;

                result.CaptureTransactionResult = checkoutapipaymentResponse.responseMessage;

            }
            else
            {
                result.CaptureTransactionResult = checkoutapipaymentResponse.message;
                result.AddError("Capture Declined" + "Error Code: " + checkoutapipaymentResponse.errorCode +
                    " " + checkoutapipaymentResponse.message);
            }

            return result;
        }


        /// <summary>
        /// Refunds a payment
        /// </summary>
        /// <param name="refundPaymentRequest">Request</param>
        /// <returns>Result</returns>
        public RefundPaymentResult Refund(RefundPaymentRequest refundPaymentRequest)
        {
            //refund includes fully refund and partial refund
            var result = new RefundPaymentResult();

            var checkoutapipaymentGateway = new GatewayConnector();
            var checkoutapipaymentRequest = new refundChargeRequest();

            // get the chargeId for refund
            string chargeId = refundPaymentRequest.Order.CaptureTransactionId;

            //build the URL
            string gatewayUrl = GatewayUrl() + "charges/" + chargeId + "/refund";
            System.Diagnostics.Debug.WriteLine("Gateway URL for Refund: " + gatewayUrl);

            checkoutapipaymentGateway.Uri = gatewayUrl;
            checkoutapipaymentGateway.Authorization = _checkoutapipaymentPaymentSettings.SecretKey;
            checkoutapipaymentRequest.amount = (Convert.ToInt32(refundPaymentRequest.AmountToRefund) * 100).ToString();
            GatewayResponse checkoutapipaymentResponse = checkoutapipaymentGateway.ProcessRequest(checkoutapipaymentRequest);

            //Check whether response code is a valid successful transaction
            Regex regex = new Regex(@"^1[0-9]+$");
            if (regex.IsMatch(checkoutapipaymentResponse.responseCode))
            {
                if (checkoutapipaymentResponse.status == "Refunded")
                {
                    var isOrderFullyRefunded = (refundPaymentRequest.AmountToRefund + refundPaymentRequest.Order.RefundedAmount == refundPaymentRequest.Order.OrderTotal);
                    result.NewPaymentStatus = isOrderFullyRefunded ? PaymentStatus.Refunded : PaymentStatus.PartiallyRefunded;
                }

            }
            else
            {
                result.AddError("Capture Declined" + "Error Code: " + checkoutapipaymentResponse.errorCode +
                    " " + checkoutapipaymentResponse.message);
            }

            return result;
        }

        /// <summary>
        /// Voids a payment
        /// </summary>
        /// <param name="voidPaymentRequest">Request</param>
        /// <returns>Result</returns>
        public VoidPaymentResult Void(VoidPaymentRequest voidPaymentRequest)
        {
            var result = new VoidPaymentResult();
            var checkoutapipaymentGateway = new GatewayConnector();
            var checkoutapipaymentRequest = new voidChargeRequest();

            // get the chargeId for refund
            string chargeId = voidPaymentRequest.Order.AuthorizationTransactionId;

            //build the URL
            string gatewayUrl = GatewayUrl() + "charges/" + chargeId + "/refund";
            System.Diagnostics.Debug.WriteLine("Gateway URL for Void: " + gatewayUrl);

            checkoutapipaymentGateway.Uri = gatewayUrl;
            checkoutapipaymentGateway.Authorization = _checkoutapipaymentPaymentSettings.SecretKey;
            checkoutapipaymentRequest.amount = (Convert.ToInt32(voidPaymentRequest.Order.OrderTotal) * 100).ToString();
            GatewayResponse checkoutapipaymentResponse = checkoutapipaymentGateway.ProcessRequest(checkoutapipaymentRequest);

            //Check whether response code is a valid successful transaction
            Regex regex = new Regex(@"^1[0-9]+$");
            if (regex.IsMatch(checkoutapipaymentResponse.responseCode))
            {
                if (checkoutapipaymentResponse.status == "Voided")
                {
                    result.NewPaymentStatus = PaymentStatus.Voided;
                }

            }
            else
            {
                result.AddError("Void Declined" + "Error Code: " + checkoutapipaymentResponse.errorCode +
                    " " + checkoutapipaymentResponse.message);
            }

            
            return result;
        }

        /// <summary>
        /// Process recurring payment
        /// </summary>
        /// <param name="processPaymentRequest">Payment info required for an order processing</param>
        /// <returns>Process payment result</returns>
        public ProcessPaymentResult ProcessRecurringPayment(ProcessPaymentRequest processPaymentRequest)
        {
            var result = new ProcessPaymentResult();
            result.AddError("Recurring payment not supported");
            return result;
        }

        /// <summary>
        /// Cancels a recurring payment
        /// </summary>
        /// <param name="cancelPaymentRequest">Request</param>
        /// <returns>Result</returns>
        public CancelRecurringPaymentResult CancelRecurringPayment(CancelRecurringPaymentRequest cancelPaymentRequest)
        {
            var result = new CancelRecurringPaymentResult();
            result.AddError("Recurring payment not supported");
            return result;
        }

        /// <summary>
        /// Gets a value indicating whether customers can complete a payment after order is placed but not completed (for redirection payment methods)
        /// </summary>
        /// <param name="order">Order</param>
        /// <returns>Result</returns>
        public bool CanRePostProcessPayment(Order order)
        {
            if (order == null)
                throw new ArgumentNullException("order");

            //it's not a redirection payment method. So we always return false
            return false;
        }

        /// <summary>
        /// Gets a route for provider configuration
        /// </summary>
        /// <param name="actionName">Action name</param>
        /// <param name="controllerName">Controller name</param>
        /// <param name="routeValues">Route values</param>
        public void GetConfigurationRoute(out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "Configure";
            controllerName = "PaymentCheckoutapipayment";
            routeValues = new RouteValueDictionary() { { "Namespaces", "Nop.Plugin.Payments.Checkoutapipayment.Controllers" }, { "area", null } };
        }

        /// <summary>
        /// Gets a route for payment info
        /// </summary>
        /// <param name="actionName">Action name</param>
        /// <param name="controllerName">Controller name</param>
        /// <param name="routeValues">Route values</param>
        public void GetPaymentInfoRoute(out string actionName, out string controllerName, out RouteValueDictionary routeValues)
        {
            actionName = "PaymentInfo";
            controllerName = "PaymentCheckoutapipayment";
            routeValues = new RouteValueDictionary() { { "Namespaces", "Nop.Plugin.Payments.Checkoutapipayment.Controllers" }, { "area", null } };
        }

       


        public Type GetControllerType()
        {
            return typeof(PaymentCheckoutapipaymentController);
        }


        public override void Install()
        {
            //settings
            var settings = new CheckoutapipaymentPaymentSettings()
            {
                IsPCI = false,
                SecretKey = "",
                PublicKey = "",
                PaymentAction = PaymentAction.Authorize,
                AutoCapTime = "0",
                Timeout = "60",
                Mode = Mode.Development

            };
            _settingService.SaveSetting(settings);

            //locales
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Notes", "Checkout.com Credit Card Payment. Please Ensure that your primary store currency is supported by Checkout.com.");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.IsPCI", "Is PCI Compliance");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.IsPCI.Hint", "Check if your website is PCI Compliant");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.SecretKey", "Secret Key");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.SecretKey.Hint", "Specify your Secret key obtained from Checkout Hub.");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PublicKey", "Public Key");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PublicKey.Hint", "Specify your Public key obtained from Checkout Hub.");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PaymentActionValues", "Payment Action");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PaymentActionValues.Hint", "Choose Payment Action");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.AutoCapTime", "Auto Capture Time (min)");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.AutoCapTime.Hint", "Enter Auto Capture Time, default is 0.");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.Timeout", "Timeout (second)");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.Timeout.Hint", "Enter Timeout, default is 60s");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.ModeValues", "Mode");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.ModeValues.Hint", "Choose Endpoint Mode");


            base.Install();
        }

        public override void Uninstall()
        {
            //settings
            _settingService.DeleteSetting<CheckoutapipaymentPaymentSettings>();

            //locales
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Notes");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.IsPCI");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.IsPCI.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.SecretKey");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.SecretKey.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PublicKey");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PublicKey.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PaymentActionValues");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.PaymentActionValues.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.AutoCapTime");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.AutoCapTime.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.Timeout");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.Timeout.Hint");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.ModeValues");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.ModeValues.Hint");

            base.Uninstall();
        }

        #endregion

        #region Properies

        /// <summary>
        /// Gets a value indicating whether capture is supported
        /// </summary>
        public bool SupportCapture
        {
            get
            {
                return true;
            }
        }

        /// <summary>
        /// Gets a value indicating whether partial refund is supported
        /// </summary>
        public bool SupportPartiallyRefund
        {
            get
            {
                return true;
            }
        }

        /// <summary>
        /// Gets a value indicating whether refund is supported
        /// </summary>
        public bool SupportRefund
        {
            get
            {
                return true;
            }
        }

        /// <summary>
        /// Gets a value indicating whether void is supported
        /// </summary>
        public bool SupportVoid
        {
            get
            {
                return true;
            }
        }

        /// <summary>
        /// Gets a recurring payment type of payment method
        /// </summary>
        public RecurringPaymentType RecurringPaymentType
        {
            get
            {
                // note that recurring payment type is set to automatic, for recurring product, it will
                // use the saved payment info for recurring payment on the recurring date.
                return RecurringPaymentType.NotSupported;
            }
        }

        /// <summary>
        /// Gets a payment method type
        /// </summary>
        public PaymentMethodType PaymentMethodType
        {
            get
            {
                return PaymentMethodType.Standard;
            }
        }

        /// <summary>
        /// Gets a value indicating whether we should display a payment information page for this plugin
        /// </summary>
        public bool SkipPaymentInfo
        {
            get
            {
                return false;
            }
        }

        #endregion

    }
}
