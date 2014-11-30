using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Nop.Plugin.Payments.Checkoutapipayment.DataTypes;

namespace Nop.Plugin.Payments.Checkoutapipayment.GatewayRequests
{
    public class fullcardChargeRequest : GatewayRequest
    {
        public string amount { set; get; }
        public string autoCapTime { set; get; }
        public string autoCapture { set; get; }
        public FullCard card { set; get; }
        public string currency { set; get; }
        public string description { set; get; }
        public string email { set; get; }
        public Shipping shippingDetails { set; get; }

    }
}
