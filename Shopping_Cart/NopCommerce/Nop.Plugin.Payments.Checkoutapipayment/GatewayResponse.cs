﻿using System;
using System.IO;
using System.Collections.Generic;
using System.Linq;
using Newtonsoft.Json.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Payments.Checkoutapipayment
{
    public class GatewayResponse
    {
        private string _responseCode;
        private string _responseMessage;
        private string _chargeId;
        private string _status;
        private string _authCode;
        private string _avsCheck;
        private string _errorCode;
        private string _message;

        public GatewayResponse(string serverJson)
        {
            //parse the json string to json object
            var gatewayResponse = JObject.Parse(@serverJson);

            //assign value to each response variable
            if (gatewayResponse["responseCode"] != null)
            {
                _responseCode = gatewayResponse["responseCode"].ToString();
                System.Diagnostics.Debug.WriteLine("Response Code " + _responseCode);

                //Dev 
                if (gatewayResponse["responseMessage"] != null)
                {
                    _responseMessage = gatewayResponse["responseMessage"].ToString();
                    System.Diagnostics.Debug.WriteLine("response Message : " + _responseMessage);
                }

                //Preprod
                if(gatewayResponse["responseShortMessage"]!=null){
                    _responseMessage = gatewayResponse["responseShortMessage"].ToString();
                    System.Diagnostics.Debug.WriteLine("response Message : " + _responseMessage);
                }

                if (gatewayResponse["id"] != null)
                {
                    _chargeId = gatewayResponse["id"].ToString();
                    System.Diagnostics.Debug.WriteLine("Charge ID: " + _chargeId);
                }

                if (gatewayResponse["status"] != null)
                {
                    _status = gatewayResponse["status"].ToString();
                    System.Diagnostics.Debug.WriteLine("Status " + _status);
                }

                if (gatewayResponse["authCode"] != null)
                {
                    _authCode = gatewayResponse["authCode"].ToString();
                    System.Diagnostics.Debug.WriteLine("AuthCode " + _authCode);
                }

                // AVS Check is different on Preprod and Dev
                //if (gatewayResponse["card"]["billingDetails"]["avsCheck"] != null)
                //{
                //    System.Diagnostics.Debug.WriteLine("AVS");
                //    _avsCheck = gatewayResponse["card"]["billingDetails"]["avsCheck"].ToString();
                //    System.Diagnostics.Debug.WriteLine("AVS Check " + _avsCheck);

                //}

                
                if(gatewayResponse["card"]["avsCheck"] !=null)
                {
                    _avsCheck = gatewayResponse["card"]["avsCheck"].ToString();
                    System.Diagnostics.Debug.WriteLine("AVS Check " + _avsCheck);
                }

                
            }
            else
            {
                if (gatewayResponse["errorCode"] != null)
                {
                    _errorCode = gatewayResponse["errorCode"].ToString();
                    System.Diagnostics.Debug.WriteLine("Error Code " + _errorCode);
                }

                if (gatewayResponse["message"] != null)
                {
                    _message = gatewayResponse["message"].ToString();
                    System.Diagnostics.Debug.WriteLine("Message " + _message);
                }
            }
        }

        public string responseCode
        {
            get { return _responseCode; }
        }

        public string responseMessage
        {
            get { return _responseMessage; }
        }

        public string chargeId
        {
            get { return _chargeId; }
        }

        public string status
        {
            get { return _status; }
        }

        public string authCode
        {
            get { return _authCode; }
        }

        public string avsCheck
        {
            get { return _avsCheck; }
        }

        public string errorCode
        {
            get { return _errorCode; }
        }

        public string message
        {
            get { return _message; }
        }
    }
}
