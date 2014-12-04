using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Nop.Plugin.Payments.Checkoutapipayment.DataTypes;

namespace Nop.Plugin.Payments.Checkoutapipayment.GatewayRequests
{
    public class cardtokenChargeRequest : GatewayRequest
    {
        public string autoCapTime { get; set; }
        public string autoCapture { get; set; }
        public string cardToken { get; set; }
        public string value { get; set; }
        public string currency { get; set; }
        public string email { get; set; }
        public string description { get; set; }
        public Shipping shippingDetails { get; set; }
    }
}
