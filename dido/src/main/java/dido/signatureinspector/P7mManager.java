package dido.signatureinspector;

import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.security.GeneralSecurityException;
import java.security.KeyFactory;
import java.security.NoSuchAlgorithmException;
import java.security.PublicKey;
import java.security.interfaces.RSAPublicKey;
import java.security.spec.InvalidKeySpecException;
import java.security.spec.X509EncodedKeySpec;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Iterator;
import java.util.List;

import org.apache.log4j.Logger;
import org.bouncycastle.asn1.ASN1ObjectIdentifier;
import org.bouncycastle.asn1.pkcs.PKCSObjectIdentifiers;
import org.bouncycastle.asn1.x500.RDN;
import org.bouncycastle.asn1.x500.X500Name;
import org.bouncycastle.asn1.x500.style.BCStyle;
import org.bouncycastle.asn1.x500.style.IETFUtils;
import org.bouncycastle.asn1.x509.SubjectPublicKeyInfo;
import org.bouncycastle.asn1.x9.X9ObjectIdentifiers;
import org.bouncycastle.cert.X509CertificateHolder;
import org.bouncycastle.cms.CMSSignedData;
import org.bouncycastle.cms.SignerInformation;
import org.bouncycastle.cms.SignerInformationStore;
import org.bouncycastle.util.Store;

import com.google.gson.Gson;
import com.itextpdf.text.DocumentException;

import dido.signatureinspector.interfaces.InterfaceSignatureManager;

public class P7mManager implements InterfaceSignatureManager {
	private List<Signature> signatures = null;
	final static Logger logger = Logger.getLogger(P7mManager.class.getName());
	private Signature tmpSignature = null;

	public boolean load(String path) {
		try{
			 File f = new File(path);
	         byte[] buffer = new byte[(int)f.length()];
	         DataInputStream in = new DataInputStream(new FileInputStream(f));
	         in.readFully(buffer);
	         in.close();
	         CMSSignedData signature = new CMSSignedData(buffer);
	         // batch verification
	         Store certs = signature.getCertificates();
	         SignerInformationStore signers = signature.getSignerInfos();
	         Collection<?> c = signers.getSigners();
	         Iterator<?> it = c.iterator();
	         signatures=new ArrayList<Signature>();
	        while (it.hasNext()){
	        	tmpSignature = new Signature();
	        	 SignerInformation signer = (SignerInformation)it.next();
	        	 Collection<?> certCollection = certs.getMatches(signer.getSID());
	        	 Iterator<?> certIt = certCollection.iterator();
	        	 X509CertificateHolder cert = (X509CertificateHolder) certIt.next();
	        	 
	        	 X500Name x500name = cert.getSubject();
	        	 
	        	 RDN cn = x500name.getRDNs(BCStyle.CN)[0];

	        	 String signerName = IETFUtils.valueToString(cn.getFirst().getValue());
	        	 logger.info("Signer Name: " + signerName);
	        	 tmpSignature.setName(signerName);
	        	 tmpSignature.setSigner(signerName);
	        	 SubjectPublicKeyInfo subjectPKInfo = cert.getSubjectPublicKeyInfo();
	        	 RSAPublicKey pub = (RSAPublicKey) toPublicKey(subjectPKInfo);
	        	 logger.info("Public Key: " + pub.getModulus().toString(16));
	        	 tmpSignature.setPublicKey(pub.getModulus().toString(16));
	        	 signatures.add(tmpSignature);
	         }	 
	        return true;
		} catch (Exception e){
			e.printStackTrace();
			return false;
		}
		
	
	}

	public String getSignatures() {

		String json = new Gson().toJson(this.signatures);
		return json;
	
	}

	public void sign(String src, String dest, String keystore, String pass)
			throws GeneralSecurityException, IOException, DocumentException {
	}
	
	private  PublicKey toPublicKey(
	        final SubjectPublicKeyInfo pkInfo)
	throws NoSuchAlgorithmException, InvalidKeySpecException {
	    X509EncodedKeySpec keyspec;
	    try {
	        keyspec = new X509EncodedKeySpec(pkInfo.getEncoded());
	    } catch (IOException e) {
	        throw new InvalidKeySpecException(e.getMessage(), e);
	    }
	    ASN1ObjectIdentifier aid = pkInfo.getAlgorithm().getAlgorithm();

	    KeyFactory kf;
	    if (PKCSObjectIdentifiers.rsaEncryption.equals(aid)) {
	        kf = KeyFactory.getInstance("RSA");
	        tmpSignature.setEncryptionAlgorithm("RSA");
	    } else if (X9ObjectIdentifiers.id_dsa.equals(aid)) {
	        kf = KeyFactory.getInstance("DSA");
	        tmpSignature.setEncryptionAlgorithm("DSA");

	    } else if (X9ObjectIdentifiers.id_ecPublicKey.equals(aid)) {
	        kf = KeyFactory.getInstance("ECDSA");
	        tmpSignature.setEncryptionAlgorithm("ECDSA");

	    } else {
	        throw new InvalidKeySpecException("unsupported key algorithm: " + aid);
	    }
      	 logger.info("key algorithm: " + tmpSignature.getEncryptionAlgorithm());

	    return kf.generatePublic(keyspec);
	}

}
