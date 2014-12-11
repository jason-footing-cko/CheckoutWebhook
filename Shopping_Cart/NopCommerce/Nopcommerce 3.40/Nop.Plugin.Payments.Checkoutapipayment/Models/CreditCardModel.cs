using System.Collections.Generic;
using System.Web.Mvc;
using Nop.Web.Framework;
using Nop.Web.Framework.Mvc;

namespace Nop.Plugin.Payments.Checkoutapipayment.Models
{
    public class CreditCardModel : BaseNopModel
    {
        [NopResourceDisplayName("cko_cc_token")]
        [AllowHtml]
        public string cko_cc_token { get; set; }

        [NopResourceDisplayName("cko_cc_email")]
        [AllowHtml]
        public string cko_cc_email { get; set; }
    }
}
