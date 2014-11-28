using System;
using System.Collections.Generic;
using System.Linq;
using System.Web.Mvc;
using Nop.Core;
using Nop.Plugin.Payments.Checkoutapipayment.Models;
using Nop.Plugin.Payments.Checkoutapipayment.Validators;
using Nop.Services.Configuration;
using Nop.Services.Localization;
using Nop.Services.Payments;
using Nop.Services.Stores;
using Nop.Web.Framework;
using Nop.Web.Framework.Controllers;

namespace Nop.Plugin.Payments.Checkoutapipayment.Controllers
{
    public class PaymentCheckoutapipaymentController : BasePaymentController
    {
        private readonly IWorkContext _workContext;
        private readonly IStoreService _storeService;
        private readonly ISettingService _settingService;
        private readonly ILocalizationService _localizationService;

        public PaymentCheckoutapipaymentController(IWorkContext workContext,
            IStoreService storeService, 
            ISettingService settingService, 
            ILocalizationService localizationService)
        {
            this._workContext = workContext;
            this._storeService = storeService;
            this._settingService = settingService;
            this._localizationService = localizationService;
        }
        
        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure()
        {
            //load settings for a chosen store scope
            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var CheckoutapipaymentPaymentSettings = _settingService.LoadSetting<CheckoutapipaymentPaymentSettings>(storeScope);

            var model = new ConfigurationModel();
            model.IsPCI = CheckoutapipaymentPaymentSettings.IsPCI;
            model.SecretKey = CheckoutapipaymentPaymentSettings.SecretKey;
            model.PublicKey = CheckoutapipaymentPaymentSettings.PublicKey;
            model.PaymentAction = Convert.ToInt32(CheckoutapipaymentPaymentSettings.PaymentAction);
            model.AutoCapTime = CheckoutapipaymentPaymentSettings.AutoCapTime;
            model.Timeout = CheckoutapipaymentPaymentSettings.Timeout;
            model.EndPoint = Convert.ToInt32(CheckoutapipaymentPaymentSettings.EndPoint);
           

            model.PaymentActionValues = CheckoutapipaymentPaymentSettings.PaymentAction.ToSelectList();
            model.EndPointValues = CheckoutapipaymentPaymentSettings.EndPoint.ToSelectList();


            model.ActiveStoreScopeConfiguration = storeScope;
            if (storeScope > 0)
            {
                model.IsPCI_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.IsPCI, storeScope);
                model.SecretKey_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.SecretKey, storeScope);
                model.PublicKey_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.PublicKey, storeScope);
                model.PaymentAction_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.PaymentAction, storeScope);
                model.AutoCapTime_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.AutoCapTime, storeScope);
                model.Timeout_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.Timeout, storeScope);
                model.EndPoint_OverrideForStore = _settingService.SettingExists(CheckoutapipaymentPaymentSettings, x => x.EndPoint, storeScope);
            }

            return View("~/Plugins/Payments.Checkoutapipayment/Views/PaymentCheckoutapipayment/Configure.cshtml", model);
        }

        [HttpPost]
        [AdminAuthorize]
        [ChildActionOnly]
        public ActionResult Configure(ConfigurationModel model)
        {
            if (!ModelState.IsValid)
                return Configure();

            //load settings for a chosen store scope
            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var CheckoutapipaymentPaymentSettings = _settingService.LoadSetting<CheckoutapipaymentPaymentSettings>(storeScope);

            //save settings
            CheckoutapipaymentPaymentSettings.IsPCI = model.IsPCI;
            CheckoutapipaymentPaymentSettings.SecretKey = model.SecretKey;
            CheckoutapipaymentPaymentSettings.PublicKey = model.PublicKey;
            CheckoutapipaymentPaymentSettings.PaymentAction = (PaymentAction)model.PaymentAction;
            CheckoutapipaymentPaymentSettings.AutoCapTime = model.AutoCapTime;
            CheckoutapipaymentPaymentSettings.Timeout = model.Timeout;
            CheckoutapipaymentPaymentSettings.EndPoint = (EndPoint)model.EndPoint;


            /* We do not clear cache after each setting update.
             * This behavior can increase performance because cached settings will not be cleared 
             * and loaded from database after each update */

            if (model.IsPCI_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.IsPCI, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.IsPCI, storeScope);

            if (model.SecretKey_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.SecretKey, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.SecretKey, storeScope);

            if (model.PublicKey_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.PublicKey, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.PublicKey, storeScope);

            if (model.PaymentAction_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.PaymentAction, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.PaymentAction, storeScope);


            if (model.AutoCapTime_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.AutoCapTime, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.AutoCapTime, storeScope);

            if (model.Timeout_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.Timeout, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.Timeout, storeScope);

            if (model.EndPoint_OverrideForStore || storeScope == 0)
                _settingService.SaveSetting(CheckoutapipaymentPaymentSettings, x => x.EndPoint, storeScope, false);
            else if (storeScope > 0)
                _settingService.DeleteSetting(CheckoutapipaymentPaymentSettings, x => x.EndPoint, storeScope);

            //now clear settings cache
            _settingService.ClearCache();

            return Configure();
        }

        [ChildActionOnly]
        public ActionResult PaymentInfo()
        {
            //load settings for a chosen store scope
            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var CheckoutapipaymentPaymentSettings = _settingService.LoadSetting<CheckoutapipaymentPaymentSettings>(storeScope);

            if (CheckoutapipaymentPaymentSettings.IsPCI)
            {
                //PCI model
                var model = new CreditCardPCIModel();

                //CC types
                model.CreditCardTypes.Add(new SelectListItem()
                {
                    Text = "Visa",
                    Value = "Visa",
                });
                model.CreditCardTypes.Add(new SelectListItem()
                {
                    Text = "Master card",
                    Value = "MasterCard",
                });
                model.CreditCardTypes.Add(new SelectListItem()
                {
                    Text = "Discover",
                    Value = "Discover",
                });
                model.CreditCardTypes.Add(new SelectListItem()
                {
                    Text = "Amex",
                    Value = "Amex",
                });

                //years
                for (int i = 0; i < 15; i++)
                {
                    string year = Convert.ToString(DateTime.Now.Year + i);
                    model.ExpireYears.Add(new SelectListItem()
                    {
                        Text = year,
                        Value = year,
                    });
                }

                //months
                for (int i = 1; i <= 12; i++)
                {
                    string text = (i < 10) ? "0" + i.ToString() : i.ToString();
                    model.ExpireMonths.Add(new SelectListItem()
                    {
                        Text = text,
                        Value = i.ToString(),
                    });
                }

                //set postback values
                var form = this.Request.Form;
                model.CardholderName = form["CardholderName"];
                model.CardNumber = form["CardNumber"];
                model.CardCode = form["CardCode"];
                var selectedCcType = model.CreditCardTypes.FirstOrDefault(x => x.Value.Equals(form["CreditCardType"], StringComparison.InvariantCultureIgnoreCase));
                if (selectedCcType != null)
                    selectedCcType.Selected = true;
                var selectedMonth = model.ExpireMonths.FirstOrDefault(x => x.Value.Equals(form["ExpireMonth"], StringComparison.InvariantCultureIgnoreCase));
                if (selectedMonth != null)
                    selectedMonth.Selected = true;
                var selectedYear = model.ExpireYears.FirstOrDefault(x => x.Value.Equals(form["ExpireYear"], StringComparison.InvariantCultureIgnoreCase));
                if (selectedYear != null)
                    selectedYear.Selected = true;

                return View("~/Plugins/Payments.Checkoutapipayment/Views/PaymentCheckoutapipayment/CreditCardPCI.cshtml", model);

            }
            else
            {
                //Checkout JS
                return null;
            }
             

        }

        [NonAction]
        public override IList<string> ValidatePaymentForm(FormCollection form)
        {
            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var CheckoutapipaymentPaymentSettings = _settingService.LoadSetting<CheckoutapipaymentPaymentSettings>(storeScope);

            if(CheckoutapipaymentPaymentSettings.IsPCI){
                var warnings = new List<string>();

                //validate
                var validator = new CreditCardPCIValidator(_localizationService);
                var model = new CreditCardPCIModel()
                {
                    CardholderName = form["CardholderName"],
                    CardNumber = form["CardNumber"],
                    CardCode = form["CardCode"],
                    ExpireMonth = form["ExpireMonth"],
                    ExpireYear = form["ExpireYear"]
                };
                var validationResult = validator.Validate(model);
                if (!validationResult.IsValid)
                    foreach (var error in validationResult.Errors)
                        warnings.Add(error.ErrorMessage);
                return warnings;
            }
            else{
                //Checkout JS
                return null;
            }
        }

        [NonAction]
        public override ProcessPaymentRequest GetPaymentInfo(FormCollection form)
        {
            var storeScope = this.GetActiveStoreScopeConfiguration(_storeService, _workContext);
            var CheckoutapipaymentPaymentSettings = _settingService.LoadSetting<CheckoutapipaymentPaymentSettings>(storeScope);

            if (CheckoutapipaymentPaymentSettings.IsPCI)
            {

                var paymentInfo = new ProcessPaymentRequest();
                paymentInfo.CreditCardType = form["CreditCardType"];
                paymentInfo.CreditCardName = form["CardholderName"];
                paymentInfo.CreditCardNumber = form["CardNumber"];
                paymentInfo.CreditCardExpireMonth = int.Parse(form["ExpireMonth"]);
                paymentInfo.CreditCardExpireYear = int.Parse(form["ExpireYear"]);
                paymentInfo.CreditCardCvv2 = form["CardCode"];
                return paymentInfo;
            }
            else
            {
                return null;
            }
        }

    }
}