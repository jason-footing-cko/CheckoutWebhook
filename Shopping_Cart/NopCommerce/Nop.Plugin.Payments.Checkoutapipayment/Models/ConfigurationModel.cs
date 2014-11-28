using System.Web.Mvc;
using Nop.Web.Framework;
using Nop.Web.Framework.Mvc;

namespace Nop.Plugin.Payments.Checkoutapipayment.Models
{
    public class ConfigurationModel : BaseNopModel
    {
        public int ActiveStoreScopeConfiguration { get; set; }

        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.IsPCI")]
        public bool IsPCI { get; set; }
        public bool IsPCI_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.SecretKey")]
        public string SecretKey { get; set; }
        public bool SecretKey_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.PublicKey")]
        public string PublicKey { get; set; }
        public bool PublicKey_OverrideForStore { get; set; }

        
        public int PaymentAction { get; set; }
        public bool PaymentAction_OverrideForStore { get; set; }
        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.PaymentActionValues")]
        public SelectList PaymentActionValues { get; set; }

        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.AutoCapTime")]
        public string AutoCapTime { get; set; }
        public bool AutoCapTime_OverrideForStore { get; set; }

        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.Timeout")]
        public string Timeout { get; set; }
        public bool Timeout_OverrideForStore { get; set; }

        public int EndPoint { get; set; }
        public bool EndPoint_OverrideForStore { get; set; }
        [NopResourceDisplayName("Plugins.Payments.Checkoutapipayment.Fields.EndPointValues")]
        public SelectList EndPointValues { get; set; }



    }
}