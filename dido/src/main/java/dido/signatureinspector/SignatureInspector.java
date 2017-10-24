package dido.signatureinspector;

import java.io.IOException;
import java.security.GeneralSecurityException;

import org.apache.log4j.Logger;

import com.itextpdf.text.DocumentException;

import dido.signatureinspector.interfaces.InterfaceSignatureManager;

public class SignatureInspector{
	final static Logger logger = Logger.getLogger(SignatureInspector.class.getName());
	InterfaceSignatureManager ism=null;

	public void loadPDF(String path){
		logger.info("Carico il PDF: "+path);
		ism= new PdfManager();
		ism.load(path);
	}
	public void loadP7m(String path){
		logger.info("Carico il P7m: "+path);

		ism= new P7mManager();
		ism.load(path);
	}

	public String getSignatures(){
		logger.info("Ritorno le firme");
		return ism.getSignatures();
	}

	public String getAnnotations(){
		if(ism instanceof PdfManager){
			logger.info("Ritorno le annotazioni");
			return ((PdfManager)ism).getAnnotations();
		}
		else{
			logger.warn("Il file non è un PDF");
			return null;
		}


	}
	
	public String getXmlMetadata(){
		if(ism instanceof PdfManager){
			logger.info("Ritorno gli XmlMetadata");
			return ((PdfManager)ism).getXmlMetadata();
		}
		else{
			logger.warn("Il file non è un PDF");
			return null;
		}


	}
	public void sign(String src, String dest,String keystore,String pass)throws GeneralSecurityException, IOException, DocumentException{
		if(ism instanceof PdfManager){
			logger.info("Firmo il pdf");
			ism.sign(src, dest, keystore, pass);
			logger.info("Pdf firmato");

		}
		else{
			logger.warn("Il file non è un PDF");
			return;
		}
	}
}

