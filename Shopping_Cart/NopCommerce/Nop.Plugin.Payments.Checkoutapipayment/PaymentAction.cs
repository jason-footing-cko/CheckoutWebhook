namespace Nop.Plugin.Payments.Checkoutapipayment
{
    /// <summary>
    /// Represents Checkout GW 3.0 payment processor payment action
    /// </summary>
    public enum PaymentAction:int
    {
        /// <summary>
        /// Authorize
        /// </summary>
        Authorize = 1,
        /// <summary>
        /// Authorize and capture
        /// </summary>
        AuthorizeAndCapture = 2
    }
}