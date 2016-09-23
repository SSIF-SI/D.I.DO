package dido.signature;


import java.io.IOException;
import java.security.GeneralSecurityException;
import java.security.Security;
import java.security.cert.X509Certificate;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;

import org.bouncycastle.asn1.pkcs.RSAPublicKey;
import org.bouncycastle.jce.provider.BouncyCastleProvider;
import org.bouncycastle.tsp.TimeStampToken;

import com.itextpdf.text.Rectangle;
import com.itextpdf.text.pdf.AcroFields;
import com.itextpdf.text.pdf.AcroFields.FieldPosition;
import com.itextpdf.text.pdf.PdfDictionary;
import com.itextpdf.text.pdf.PdfName;
import com.itextpdf.text.pdf.PdfReader;
import com.itextpdf.text.pdf.PdfString;
import com.itextpdf.text.pdf.security.CertificateInfo;
import com.itextpdf.text.pdf.security.PdfPKCS7;
import com.itextpdf.text.pdf.security.SignaturePermissions;
import com.itextpdf.text.pdf.security.SignaturePermissions.FieldLock;

import dido.signature.interfaces.iSignatureManager;
//TODO: DA COMPLETARE!! Ho solo copiato i metodi dagli esempi di utilizzo della libreria iText,forse sono da integrare con altre funzionalità.

public class SignatureManager implements iSignatureManager {
	private String pdfPath = null;
	private List<Signature> signatures = null;
	private Signature tmpSignature = null;
	
	public PdfPKCS7 verifySignature(AcroFields fields, String name) throws GeneralSecurityException, IOException {
		System.out.println("Signature covers whole document: " + fields.signatureCoversWholeDocument(name));
		this.tmpSignature.setName(name);
		
		System.out.println("Document revision: " + fields.getRevision(name) + " of " + fields.getTotalRevisions());
		this.tmpSignature.setRevision(fields.getRevision(name));
		this.tmpSignature.setTotalREvisions(fields.getTotalRevisions());
        
		PdfPKCS7 pkcs7 = fields.verifySignature(name);
        System.out.println("Integrity check OK? " + pkcs7.verify());
        this.tmpSignature.setComplete(pkcs7.verify());
        
        return pkcs7;
	}
 
	public void verifySignatures(String path) throws IOException, GeneralSecurityException {
		System.out.println(path);
	        PdfReader reader = new PdfReader(path);
	        AcroFields fields = reader.getAcroFields();
	        ArrayList<String> names = fields.getSignatureNames();
			for (String name : names) {
				this.tmpSignature = new Signature();
				System.out.println("===== " + name + " =====");
				verifySignature(fields, name);
				this.signatures.add(this.tmpSignature);
			}
		System.out.println();
	}
	
	public SignaturePermissions inspectSignature(AcroFields fields, String name, SignaturePermissions perms) throws GeneralSecurityException, IOException {
		List<FieldPosition> fps = fields.getFieldPositions(name);
		if (fps != null && fps.size() > 0) {
			FieldPosition fp = fps.get(0);
			Rectangle pos = fp.position;
			if (pos.getWidth() == 0 || pos.getHeight() == 0) {
				System.out.println("Invisible signature");
				this.tmpSignature.setInvisible(true);
			}
			else {
				this.tmpSignature.setInvisible(false);
				System.out.println(String.format("Field on page %s; llx: %s, lly: %s, urx: %s; ury: %s",
					fp.page, pos.getLeft(), pos.getBottom(), pos.getRight(), pos.getTop()));
			}
		}
 
		PdfPKCS7 pkcs7 = this.verifySignature(fields, name);
		
		System.out.println("Digest algorithm: " + pkcs7.getHashAlgorithm());
		this.tmpSignature.setHashAlgorithm(pkcs7.getHashAlgorithm());
		
		System.out.println("Encryption algorithm: " + pkcs7.getEncryptionAlgorithm());
		this.tmpSignature.setEncryptionAlgorithm(pkcs7.getEncryptionAlgorithm());
		
		System.out.println("Filter subtype: " + pkcs7.getFilterSubtype());
		this.tmpSignature.setFilterSubtype(pkcs7.getFilterSubtype().toString());
		
		X509Certificate cert = (X509Certificate) pkcs7.getSigningCertificate();
		System.out.println("Name of the signer: " + CertificateInfo.getSubjectFields(cert).getField("CN"));
		this.tmpSignature.setSigner(CertificateInfo.getSubjectFields(cert).getField("CN"));
		
		RSAPublicKey pub = (RSAPublicKey) cert.getPublicKey();
		System.out.println("Public Key: " + pub.getModulus().toString(16));
		this.tmpSignature.setPublicKey(pub.getModulus().toString(16));
		
		if (pkcs7.getSignName() != null){
			System.out.println("Alternative name of the signer: " + pkcs7.getSignName());
			this.tmpSignature.setSignerAlt(pkcs7.getSignName());
		} else {
			this.tmpSignature.setSignerAlt(null);
		}
		
		SimpleDateFormat date_format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SS");
		System.out.println("Signed on: " + date_format.format(pkcs7.getSignDate().getTime()));
		this.tmpSignature.setSignDate(date_format.format(pkcs7.getSignDate().getTime()));
		
		if (pkcs7.getTimeStampDate() != null) {
			System.out.println("TimeStamp: " + date_format.format(pkcs7.getTimeStampDate().getTime()));
			TimeStampToken ts = pkcs7.getTimeStampToken();
			System.out.println("TimeStamp service: " + ts.getTimeStampInfo().getTsa());
			System.out.println("Timestamp verified? " + pkcs7.verifyTimestampImprint());
		}
		System.out.println("Location: " + pkcs7.getLocation());
		System.out.println("Reason: " + pkcs7.getReason());
		PdfDictionary sigDict = fields.getSignatureDictionary(name);
		PdfString contact = sigDict.getAsString(PdfName.CONTACTINFO);
		if (contact != null)
			System.out.println("Contact info: " + contact);
		perms = new SignaturePermissions(sigDict, perms);
		
		System.out.println("Signature type: " + (perms.isCertification() ? "certification" : "approval"));
		this.tmpSignature.setSignType(perms.isCertification() ? "certification" : "approval");
		
		System.out.println("Filling out fields allowed: " + perms.isFillInAllowed());
		System.out.println("Adding annotations allowed: " + perms.isAnnotationsAllowed());
		for (FieldLock lock : perms.getFieldLocks()) {
			System.out.println("Lock: " + lock.toString());
		}
        return perms;
	}
 
	public void inspectSignatures(String path) throws IOException, GeneralSecurityException {
		System.out.println(path);
        PdfReader reader = new PdfReader(path);
        AcroFields fields = reader.getAcroFields();
        ArrayList<String> names = fields.getSignatureNames();
        SignaturePermissions perms = null;
		for (String name : names) {
			System.out.println("===== " + name + " =====");
			perms = inspectSignature(fields, name, perms);
		}
		System.out.println();
	}
 
	public boolean loadPDF(String path){
		SignatureManager app = new SignatureManager();
		BouncyCastleProvider provider = new BouncyCastleProvider();
		Security.addProvider(provider);
		try {
			app.inspectSignatures(this.pdfPath);
		} catch (IOException e) {
			e.printStackTrace();
			return false;
		} catch (GeneralSecurityException e) {
			e.printStackTrace();
			return false;
		}
		return true;
	}
	
	public List<Signature> getSignatures() {
		return this.signatures;
	}
}