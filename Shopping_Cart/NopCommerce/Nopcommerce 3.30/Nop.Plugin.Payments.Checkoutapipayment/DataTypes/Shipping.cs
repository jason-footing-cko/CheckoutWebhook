using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Payments.Checkoutapipayment.DataTypes
{
    public class Shipping
    {
        public string addressLine1 { get; set; }
        public string addressLine2 { get; set; }
        public string postcode { get; set; }
        public string country { get; set; }
        public string city { get; set; }
        public string state { get; set; }
        public string phone { get; set; }
        public string recipientName { get; set; }
    }
}
