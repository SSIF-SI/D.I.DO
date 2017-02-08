package dido.signatureTests;

//Tipo A.pdf Tipo A-1 commento.pdf	Tipo A-2 comment1.pdf Tipo A-ex 2 commenti.pdf Tipo NON A.pdf

import java.io.IOException;
import java.security.GeneralSecurityException;

import org.apache.log4j.BasicConfigurator;

import com.itextpdf.text.DocumentException;

import dido.pdfmanager.PdfManager;

public class SignatureTest1 {
	public static final String SRC = "/testresources/TipoA.pdf";	
	public static final String SRC1 = "/testresources/TipoA-1commento.pdf";
	public static final String SRC2 = "/testresources/TipoA-2comment1.pdf";
	public static final String SRC3 = "/testresources/TipoA-ex2commenti.pdf";
	public static final String SRC4 = "/testresources/TipoNONA.pdf";
	public static final String SRC5 = "/testresources/sample06.pdf";
	public static final String KEYSTORE = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/chiaveRSA.ks";
	public static final String PASSWORD = "provakey";

	public static final String SRC6 = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/TipoA.pdf";	
	public static final String SRC7 = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/TipoASigned.pdf";	





	public static void main(String[] args) throws IOException, DocumentException, GeneralSecurityException {
		System.out.println("INIZIO TEST");
		BasicConfigurator.configure();
		PdfManager sigMan=new PdfManager();
		sigMan.loadPDF(SRC);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC1);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC2);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC3);
		sigMan.getAnnotations();

		sigMan.loadPDF(SRC4);
		sigMan.getAnnotations();


		sigMan.loadPDF(SRC5);
		sigMan.getAnnotations();
		sigMan.sign(SRC6, SRC7, KEYSTORE, PASSWORD);
		sigMan.loadPDF(SRC7);
		sigMan.getAnnotations();
	}

}
