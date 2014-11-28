using Nop.Core.Configuration;

namespace Nop.Plugin.Payments.Checkoutapipayment
{
    public class CheckoutapipaymentPaymentSettings : ISettings
    {
        public bool IsPCI { get; set; }
        public string SecretKey { get; set; }
        public string PublicKey { get; set; }
        public PaymentAction PaymentAction { get; set; }
        public string AutoCapTime { get; set; }
        public string Timeout { get; set; }
        public EndPoint EndPoint { get; set; }
    }
}
