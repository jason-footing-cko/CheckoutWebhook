using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Globalization;
using System.Net;
using System.Text;
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
            return result;
        }


        /// <summary>
        /// Refunds a payment
        /// </summary>
        /// <param name="refundPaymentRequest">Request</param>
        /// <returns>Result</returns>
        public RefundPaymentResult Refund(RefundPaymentRequest refundPaymentRequest)
        {
            var result = new RefundPaymentResult();

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
            result.AddError("Void method not supported");
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
                EndPoint = EndPoint.Development

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
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.EndPointValues", "Endpoint");
            this.AddOrUpdatePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.EndPointValues.Hint", "Choose Endpoint Mode");


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
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.EndPointValues");
            this.DeletePluginLocaleResource("Plugins.Payments.Checkoutapipayment.Fields.EndPointValues.Hint");

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
                return false;
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
