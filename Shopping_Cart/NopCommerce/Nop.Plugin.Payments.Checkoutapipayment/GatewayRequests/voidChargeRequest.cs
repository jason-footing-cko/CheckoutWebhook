using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Payments.Checkoutapipayment.GatewayRequests
{
    public class voidChargeRequest : GatewayRequest
    {
        public string amount { set; get; }
    }
}
