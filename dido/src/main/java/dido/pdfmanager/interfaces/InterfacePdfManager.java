package dido.pdfmanager.interfaces;

import java.io.IOException;
import java.security.GeneralSecurityException;

import com.itextpdf.text.DocumentException;


public interface InterfacePdfManager {
	public boolean loadPDF(String path);
	public String getSignatures();
	public void sign(String src, String dest,String keystore,String pass)throws GeneralSecurityException, IOException, DocumentException ;
}
