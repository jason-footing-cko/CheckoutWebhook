using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Nop.Plugin.Payments.Checkoutapipayment.DataTypes;

namespace Nop.Plugin.Payments.Checkoutapipayment.DataTypes
{
    public class FullCard
    {
        public string cvv { get; set; }
        public string expiryMonth { get; set; }
        public string expiryYear { get; set; }
        public string name { get; set; }
        public string number { get; set; }
        public Billing billingDetails { get; set; }
    }
}
