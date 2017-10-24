package dido.signatureinspector.interfaces;

import java.io.IOException;
import java.security.GeneralSecurityException;

import com.itextpdf.text.DocumentException;


public interface InterfaceSignatureManager {
	public boolean load(String path);
	public String getSignatures();
	public void sign(String src, String dest,String keystore,String pass)throws GeneralSecurityException, IOException, DocumentException ;
}
