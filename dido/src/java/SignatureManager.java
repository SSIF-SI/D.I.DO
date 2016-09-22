
import com.itextpdf.text.pdf.AcroFields;
import com.itextpdf.text.pdf.AcroFields.Item;
import com.itextpdf.text.pdf.PRStream;
import com.itextpdf.text.pdf.PdfDictionary;
import com.itextpdf.text.pdf.PdfName;
import com.itextpdf.text.pdf.PdfReader;
import com.itextpdf.text.pdf.PdfStream;

import java.io.IOException;
//TODO: DA COMPLETARE!! Ho solo copiato i metodi dagli esempi di utilizzo della libreria iText,forse sono da integrare con altre funzionalità.
public class SignatureManager {
	public static  String src;

	public SignatureManager(String src){
		this.src=src;
	}
	protected PdfPKCS7 verifySignature(AcroFields fields, String name) throws GeneralSecurityException, IOException {
		System.out.println("Signature covers whole document: " + fields.signatureCoversWholeDocument(name));
		System.out.println("Document revision: " + fields.getRevision(name) + " of " + fields.getTotalRevisions());
		PdfPKCS7 pkcs7 = fields.verifySignature(name);
		System.out.println("Integrity check OK? " + pkcs7.verify());
		return pkcs7;
	}

	public String verifySignatures(String path) throws IOException, GeneralSecurityException {
		System.out.println(path);
		PdfReader reader = new PdfReader(path);
		AcroFields fields = reader.getAcroFields();
		ArrayList<String> names = fields.getSignatureNames();
		String result="";
		PdfPKCS7 tmp=null;
		for (String name : names) {
			System.out.println("===== " + name + " =====");
			tmp=verifySignature(fields, name);
			result=name + "Integrity check OK?"+ tmp.verify();

		}
		return	result;
	}

	protected SignaturePermissions inspectSignature(AcroFields fields, String name, SignaturePermissions perms) throws GeneralSecurityException, IOException {
		List<FieldPosition> fps = fields.getFieldPositions(name);
		if (fps != null && fps.size() > 0) {
			FieldPosition fp = fps.get(0);
			Rectangle pos = fp.position;
			if (pos.getWidth() == 0 || pos.getHeight() == 0) {
				System.out.println("Invisible signature");
			}
			else {
				System.out.println(String.format("Field on page %s; llx: %s, lly: %s, urx: %s; ury: %s",
						fp.page, pos.getLeft(), pos.getBottom(), pos.getRight(), pos.getTop()));
			}
		}

		PdfPKCS7 pkcs7 =verifySignature(fields, name);
		System.out.println("Digest algorithm: " + pkcs7.getHashAlgorithm());
		System.out.println("Encryption algorithm: " + pkcs7.getEncryptionAlgorithm());
		System.out.println("Filter subtype: " + pkcs7.getFilterSubtype());
		X509Certificate cert = (X509Certificate) pkcs7.getSigningCertificate();
		System.out.println("Name of the signer: " + CertificateInfo.getSubjectFields(cert).getField("CN"));
		if (pkcs7.getSignName() != null)
			System.out.println("Alternative name of the signer: " + pkcs7.getSignName());
		SimpleDateFormat date_format = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss.SS");
		System.out.println("Signed on: " + date_format.format(pkcs7.getSignDate().getTime()));
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
}