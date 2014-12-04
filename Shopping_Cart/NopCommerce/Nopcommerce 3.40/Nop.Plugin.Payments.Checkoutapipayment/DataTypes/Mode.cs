namespace Nop.Plugin.Payments.Checkoutapipayment.DataTypes
{
    /// <summary>
    /// Represents Checkout GW 3.0 payment Endpoints
    /// </summary>
    public enum Mode:int
    {
        Development = 1,
        Preprod = 2,
        Live = 3
    }
}