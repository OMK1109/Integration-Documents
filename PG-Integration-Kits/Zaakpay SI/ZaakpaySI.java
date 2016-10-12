import java.io.InputStream;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import javax.net.ssl.HostnameVerifier;
import javax.net.ssl.HttpsURLConnection;
import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSession;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;
import java.security.cert.X509Certificate;

public class ZaakpaySI {

	

	public static void main(String[] args) {
		try {

			String encoding = "UTF-8";

			String url = "http://205.147.110.196:8280/transactSI?v=3"; // Zaakpay Standing Instruction API URL
			URL apiURL = new URL(url);
			
			
			TrustManager[] trustAllCerts = new TrustManager[] {new X509TrustManager() {
				public java.security.cert.X509Certificate[] getAcceptedIssuers() {
					return null;
				}
				public void checkClientTrusted(X509Certificate[] certs, String authType) {
				}
				public void checkServerTrusted(X509Certificate[] certs, String authType) {
				}
			}
		};

		// Install the all-trusting trust manager
		SSLContext sc = SSLContext.getInstance("SSL");
		sc.init(null, trustAllCerts, new java.security.SecureRandom());
		HttpsURLConnection.setDefaultSSLSocketFactory(sc.getSocketFactory());

		// Create all-trusting host name verifier
		HostnameVerifier allHostsValid = new HostnameVerifier() {
			public boolean verify(String hostname, SSLSession session) {
				return true;
			}
		};

		// Install the all-trusting host verifier
		HttpsURLConnection.setDefaultHostnameVerifier(allHostsValid);
		
		
		/*	HttpsURLConnection
			.setDefaultHostnameVerifier(new AllowAllHostnameVerifier());
			*/
			HttpURLConnection con = (HttpsURLConnection) apiURL.openConnection();

			con.setRequestMethod("POST");
			con.setDoOutput(true);
			con.setRequestProperty("Content-Type",
					"application/x-www-form-urlencoded;charset=" + encoding);
			
			String orderid=System.currentTimeMillis()+"";
			
			// Checksum string using 7 parameters: amount,ip,txndate,currency,zaakpay merchant identifier,orderid,mode
			String checksumString="'1000''117.241.88.45''2014-11-11''INR''b19e8f103bce406cbd3476431b6b7973''"+orderid+"''0'"; 
			
			String checksum=calculateChecksum("0678056d96914a8583fb518caf42828a", checksumString);

			// Query string constructed using all parameters to be posted to Zaakpay
			// Order of parameters in checksum string and query string must be same 
			String queryString = "amount=1000&merchantIpAddress=117.241.88.45&txnDate=2014-11-11&currency=INR&merchantIdentifier=b19e8f103bce406cbd3476431b6b7973&orderId="+orderid+"&mode=0&showMobile=FALSE&buyerEmail=chirag@zaakpay.com&buyerFirstName=chirag&buyerLastName=jain&buyerAddress=udyogvihar&buyerCity=gurgaon&buyerState=Dilli&buyerCountry=India&buyerPincode=110075&buyerPhoneNumber=9968403303&txnType=1&zpPayOption=1&purpose=1&productDescription=ZaakpaySI&shipToAddress=udyogvihar&shipToCity=gurgaon&shipToState=Haryana&shipToCountry=India&shipToPincode=110075&shipToPhoneNumber=9911001199&shipToFirstname=chirag&shipToLastname=jain&merchantCardRefId=cardRef1&debitorcredit=credit&checksum="+checksum;

			OutputStream oStream = con.getOutputStream();

			oStream.write(queryString.getBytes(encoding));
			if (null != oStream)
				oStream.close();

			InputStream instream = con.getInputStream();

			StringBuilder builder = new StringBuilder();
			byte[] b = new byte[4096];
			for (int n; (n = instream.read(b)) != -1;) {
				builder.append(new String(b, 0, n));
			}

			String responseString = builder.toString();
			System.out.println("response " + responseString);
		} catch (Exception e) {
			e.printStackTrace();
			System.out.println("Failure");
		}

	}

	private static String toHex(byte[] bytes) {
		StringBuilder buffer = new StringBuilder(bytes.length * 2);
		String str;
		for (Byte b : bytes) {
			str = Integer.toHexString(b);
			int len = str.length();
			if (len == 8) {
				buffer.append(str.substring(6));
			} else if (str.length() == 2) {
				buffer.append(str);
			} else {
				buffer.append("0" + str);
			}
		}
		return buffer.toString();
	}

	public static String calculateChecksum(String secretKey,
			String allParamValue) throws Exception {
		byte[] dataToEncryptByte = allParamValue.getBytes();
		byte[] keyBytes = secretKey.getBytes();
		SecretKeySpec secretKeySpec = new SecretKeySpec(keyBytes, "HmacSHA256");
		Mac mac = Mac.getInstance("HmacSHA256");
		mac.init(secretKeySpec);
		byte[] checksumByte = mac.doFinal(dataToEncryptByte);
		String checksum = toHex(checksumByte);
		// String checksum = new String(checksumByte);
		return checksum;
	}

}
