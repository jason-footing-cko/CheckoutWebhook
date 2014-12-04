using System.IO;
using System.Net;
using System.Text;
using Newtonsoft.Json;
using Nop.Core.Configuration;
using Nop.Plugin.Payments.Checkoutapipayment.GatewayRequests;


namespace Nop.Plugin.Payments.Checkoutapipayment
{
    public class GatewayConnector
    {
        string _uri = string.Empty;
        //gateway default timeout value 60 second = 60 ms
        int _timeout = 60000;
        string _authorization = string.Empty;
        string _mode = string.Empty;

        /// <summary>
        /// The Uri of the Checkout payment gateway
        /// </summary>
        public string Uri
        {
            get { return _uri; }
            set { _uri = value; }
        }

        /// <summary>
        /// The connection timeout
        /// </summary>
        public int ConnectionTimeout
        {
            get { return _timeout; }
            set { _timeout = value; }
        }

        /// <summary>
        /// The authorization header
        /// </summary>
        public string Authorization
        {
            get { return _authorization; }
            set { _authorization = value; }
        }


        /// <summary>
        /// Do the post to the gateway and retrieve the response
        /// </summary>
        /// 
        public GatewayResponse ProcessRequest(GatewayRequest Request)
        {
            HttpWebRequest request = (HttpWebRequest)HttpWebRequest.Create(_uri);
            //creating charge is a POST
            request.Method = "POST";
            request.Timeout = _timeout;
            request.ContentType = "application/json; charset=utf-8";
            request.Headers.Add("Authorization", _authorization);
            //request.KeepAlive = false;

            // Transform Gateway Request into Json String
            string gatewayRequest = JsonConvert.SerializeObject(Request);
            System.Diagnostics.Debug.WriteLine(gatewayRequest);

            using (var streamWriter = new StreamWriter(request.GetRequestStream()))
            {
                string json = gatewayRequest;

                streamWriter.Write(json);

                streamWriter.Flush();
                streamWriter.Close();
            }

            HttpWebResponse response = (HttpWebResponse)request.GetResponse();
            

            using (var streamReader = new StreamReader(response.GetResponseStream()))
            {
                string _serverJson = streamReader.ReadToEnd();

                System.Diagnostics.Debug.WriteLine(_serverJson);

                return new GatewayResponse(_serverJson);
            }
        }



    }
}
