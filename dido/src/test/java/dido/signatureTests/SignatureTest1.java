package dido.signatureTests;

//Tipo A.pdf Tipo A-1 commento.pdf	Tipo A-2 comment1.pdf Tipo A-ex 2 commenti.pdf Tipo NON A.pdf

import java.io.IOException;
import java.security.GeneralSecurityException;

import org.apache.log4j.BasicConfigurator;

import com.itextpdf.text.DocumentException;

import dido.signatureinspector.P7mManager;
import dido.signatureinspector.PdfManager;
import dido.signatureinspector.SignatureInspector;

public class SignatureTest1 {
	public static final String SRC = "/testresources/TipoA.pdf";	
//	public static final String SRC1 = "/testresources/TipoA-1commento.pdf";
	public static final String SRC2 = "/testresources/TipoA-2comment1.pdf";
	public static final String SRC3 = "/testresources/TipoA-ex2commenti.pdf";
//	public static final String SRC4 = "/testresources/TipoNONA.pdf";
	public static final String SRC1 = "/testresources/Mandato n. 109385.pdf";
	public static final String KEYSTORE = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/signsotreT.jks";
	public static final String PASSWORD = "PROVACHIAVE";

	public static final String SRC4 = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/3_costi_del_personale_BARONTI.xls.p7m";	
	public static final String SRC5 = "/home/giuseppe/git/D.I.DO/dido/src/test/java/testresources/All.4_DSAN_Mandati_BustePaga_F24_CNR_INTESA.pdf.p7m";	





	public static void main(String[] args) throws IOException, DocumentException, GeneralSecurityException {
		System.out.println("INIZIO TEST");
		BasicConfigurator.configure();
//		PdfManager sigMan=new PdfManager();
//		P7mManager p7M=new P7mManager();
		SignatureInspector sigIn=new SignatureInspector();
		sigIn.loadPDF(SRC1);
		System.out.println("JSON PDF:"+ sigIn.getAnnotations());
		System.out.println("JSON PDF:"+ sigIn.getSignatures());
//		sigIn.loadP7m(SRC4);
//		System.out.println("JSON P7m:"+ sigIn.getAnnotations());
//		System.out.println("JSON P7m:"+ sigIn.getSignatures());
		
//		sigMan.load(SRC);
//		sigMan.getAnnotations();

//		sigMan.loadPDF(SRC1);
//		sigMan.getAnnotations();

//		sigMan.loadPDF(SRC2);
//		sigMan.getAnnotations();

//		sigMan.loadPDF(SRC3);
//		sigMan.getAnnotations();

//		sigMan.load(SRC4);
//		sigMan.getAnnotations();
//
//
//		sigMan.load(SRC5);
//		sigMan.getAnnotations();
//		
//		p7M.load(SRC5);
//		p7M.getSignatures();
//		
//		p7M.load(SRC4);
//		p7M.getSignatures();
		
//		sigMan.sign(SRC6, SRC7, KEYSTORE, PASSWORD);
//		sigMan.loadPDF(SRC7);
//		sigMan.getAnnotations();
	}

}
