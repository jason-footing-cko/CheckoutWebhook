using System.Collections.Generic;
using System.Web.Mvc;
using Nop.Web.Framework;
using Nop.Web.Framework.Mvc;

namespace Nop.Plugin.Payments.Checkoutapipayment.Models
{
    public class CreditCardModel : BaseNopModel
    {
        public string cko_cc_token { get; set; }
        public string cko_cc_email { get; set; }
    }
}
