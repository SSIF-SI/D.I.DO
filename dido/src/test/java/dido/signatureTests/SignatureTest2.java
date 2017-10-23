package dido.signatureTests;

import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;

import java.security.KeyFactory;
import java.security.NoSuchAlgorithmException;

import java.security.PublicKey;

import java.security.interfaces.RSAPublicKey;
import java.security.spec.InvalidKeySpecException;
import java.security.spec.X509EncodedKeySpec;
import java.util.Collection;

import java.util.Iterator;


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



class SignatureTest2{
	
	public static final String TEST_FILENAME = "/home/federico/Scaricati/3_costi_del_personale_FERRO.xls.p7m";
	
	public static void main(String[] args){
		try{
			 File f = new File(TEST_FILENAME);
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
	         
	        while (it.hasNext()){
	        	 SignerInformation signer = (SignerInformation)it.next();
	        	 Collection<?> certCollection = certs.getMatches(signer.getSID());
	        	 Iterator<?> certIt = certCollection.iterator();
	        	 X509CertificateHolder cert = (X509CertificateHolder) certIt.next();
	        	 
	        	 X500Name x500name = cert.getSubject();
	        	 
	        	 RDN cn = x500name.getRDNs(BCStyle.CN)[0];

	        	 String signerName = IETFUtils.valueToString(cn.getFirst().getValue());
	        	 System.out.println(signerName);
	        	 
	        	 SubjectPublicKeyInfo subjectPKInfo = cert.getSubjectPublicKeyInfo();
	        	 RSAPublicKey pub = (RSAPublicKey) toPublicKey(subjectPKInfo);
	        	 System.out.println(pub.getModulus().toString(16));
	         }	 
		} catch (Exception e){
			e.printStackTrace();
		}
	}
	
	public static PublicKey toPublicKey(
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
	    } else if (X9ObjectIdentifiers.id_dsa.equals(aid)) {
	        kf = KeyFactory.getInstance("DSA");
	    } else if (X9ObjectIdentifiers.id_ecPublicKey.equals(aid)) {
	        kf = KeyFactory.getInstance("ECDSA");
	    } else {
	        throw new InvalidKeySpecException("unsupported key algorithm: " + aid);
	    }

	    return kf.generatePublic(keyspec);
	}
	
}